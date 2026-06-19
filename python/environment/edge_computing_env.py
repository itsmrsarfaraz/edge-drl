"""
EdgeComputingEnv — Custom Gymnasium Environment
================================================
Simulates resource allocation across N edge nodes for IoT tasks.

State  : [cpu_usage_n1, mem_usage_n1, queue_n1, ..., task_cpu, task_mem, task_priority]
Action : integer in [0, num_nodes] where num_nodes = "delay task"
Reward : shaped reward encouraging low latency, balanced load, short queues
"""

import numpy as np
import gymnasium as gym
from gymnasium import spaces


class EdgeComputingEnv(gym.Env):
    """
    Custom Gymnasium environment for DRL-based resource allocation
    in edge computing simulations.

    Parameters
    ----------
    num_nodes       : number of edge nodes in the simulation
    max_queue       : maximum tasks a node can queue before overload
    max_steps       : episode length (number of tasks to allocate)
    cpu_capacities  : list of CPU capacities per node (0–100 %)
    mem_capacities  : list of memory capacities per node (MB)
    """

    metadata = {"render_modes": []}

    def __init__(
        self,
        num_nodes: int = 3,
        max_queue: int = 10,
        max_steps: int = 50,
        cpu_capacities: list = None,
        mem_capacities: list = None,
    ):
        super().__init__()

        self.num_nodes      = num_nodes
        self.max_queue      = max_queue
        self.max_steps      = max_steps
        self.current_step   = 0

        # Default capacities if not provided
        self.cpu_capacities = cpu_capacities or [100.0] * num_nodes
        self.mem_capacities = mem_capacities or [8192.0] * num_nodes

        # ── Action Space ───────────────────────────────────────
        # 0 … num_nodes-1 : assign task to node N
        # num_nodes        : delay the task
        self.action_space = spaces.Discrete(num_nodes + 1)

        # ── Observation Space ──────────────────────────────────
        # Per node: [cpu_util %, mem_util %, queue_ratio]  → 3 values each
        # Task:     [cpu_req %, mem_req_ratio, priority_norm, deadline_norm]
        # Total dims: num_nodes * 3 + 4
        obs_dim = num_nodes * 3 + 4
        self.observation_space = spaces.Box(
            low   = np.zeros(obs_dim, dtype=np.float32),
            high  = np.ones(obs_dim,  dtype=np.float32),
            dtype = np.float32,
        )

        # ── Internal State ─────────────────────────────────────
        self._reset_state()

        # ── Episode tracking ───────────────────────────────────
        self.episode_rewards  = []
        self.episode_latencies = []
        self.allocation_log   = []   # [{task, node, reward, latency}, ...]

    # ──────────────────────────────────────────────────────────
    # Gym API
    # ──────────────────────────────────────────────────────────

    def reset(self, *, seed=None, options=None):
        super().reset(seed=seed)

        self._reset_state()
        self.current_step    = 0
        self.episode_rewards = []
        self.episode_latencies = []
        self.allocation_log  = []

        # Generate first task
        self.current_task = self._generate_task()

        obs  = self._get_observation()
        info = {}
        return obs, info

    def step(self, action: int):
        assert self.action_space.contains(action), f"Invalid action: {action}"

        task      = self.current_task
        reward    = 0.0
        latency   = 0.0
        truncated = False

        if action < self.num_nodes:
            # ── Assign task to node `action` ───────────────────
            node_idx = action
            reward, latency = self._assign_task(task, node_idx)
        else:
            # ── Delay the task ─────────────────────────────────
            reward  = self._delay_penalty(task)
            latency = task["deadline"] * 1000  # penalise with full deadline as latency

        self.episode_rewards.append(reward)
        self.episode_latencies.append(latency)
        self.allocation_log.append({
            "step":    self.current_step,
            "action":  int(action),
            "reward":  round(float(reward), 4),
            "latency": round(float(latency), 2),
        })

        self.current_step += 1
        terminated = self.current_step >= self.max_steps

        if not terminated:
            self.current_task = self._generate_task()

        obs  = self._get_observation()
        info = {
            "latency":       latency,
            "action":        int(action),
            "step":          self.current_step,
            "node_loads":    self._get_node_loads(),
        }

        return obs, reward, terminated, truncated, info

    def render(self):
        pass  # headless — no rendering needed

    def close(self):
        pass

    # ──────────────────────────────────────────────────────────
    # Internal helpers
    # ──────────────────────────────────────────────────────────

    def _reset_state(self):
        """Reset all node resource counters."""
        self.node_cpu_used   = np.zeros(self.num_nodes, dtype=np.float32)
        self.node_mem_used   = np.zeros(self.num_nodes, dtype=np.float32)
        self.node_queue      = np.zeros(self.num_nodes, dtype=np.int32)
        self.current_task    = None

    def _generate_task(self) -> dict:
        """
        Randomly generate a task.
        Priority distribution mirrors the Python task generator:
        low 25 %, medium 45 %, high 20 %, critical 10 %
        """
        priorities = ["low", "medium", "high", "critical"]
        weights    = [0.25, 0.45, 0.20, 0.10]
        priority   = self.np_random.choice(priorities, p=weights)

        profiles = {
            "low":      {"cpu": (2,  12),  "mem": (64,  256),  "deadline": (8,  20)},
            "medium":   {"cpu": (5,  25),  "mem": (128, 512),  "deadline": (4,  10)},
            "high":     {"cpu": (15, 45),  "mem": (256, 1024), "deadline": (2,   6)},
            "critical": {"cpu": (30, 70),  "mem": (512, 2048), "deadline": (0.5, 3)},
        }
        p = profiles[priority]

        return {
            "priority":    priority,
            "priority_num": priorities.index(priority),   # 0-3
            "cpu_req":     float(self.np_random.uniform(*p["cpu"])),
            "mem_req":     float(self.np_random.uniform(*p["mem"])),
            "deadline":    float(self.np_random.uniform(*p["deadline"])),
        }

    def _get_observation(self) -> np.ndarray:
        """
        Build the flat observation vector.
        All values normalised to [0, 1].
        """
        obs = []

        for i in range(self.num_nodes):
            cpu_util   = self.node_cpu_used[i] / max(self.cpu_capacities[i], 1.0)
            mem_util   = self.node_mem_used[i] / max(self.mem_capacities[i], 1.0)
            queue_ratio = self.node_queue[i]   / max(self.max_queue, 1)

            obs.extend([
                float(np.clip(cpu_util,    0.0, 1.0)),
                float(np.clip(mem_util,    0.0, 1.0)),
                float(np.clip(queue_ratio, 0.0, 1.0)),
            ])

        if self.current_task:
            task = self.current_task
            obs.extend([
                float(np.clip(task["cpu_req"]  / 100.0,   0.0, 1.0)),
                float(np.clip(task["mem_req"]  / 8192.0,  0.0, 1.0)),
                float(task["priority_num"] / 3.0),
                float(np.clip(1.0 - task["deadline"] / 20.0, 0.0, 1.0)),  # urgency
            ])
        else:
            obs.extend([0.0, 0.0, 0.0, 0.0])

        return np.array(obs, dtype=np.float32)

    def _assign_task(self, task: dict, node_idx: int) -> tuple[float, float]:
        """
        Assign a task to a node, update state, compute reward + latency.
        Returns (reward, latency_ms).
        """
        cpu_cap = self.cpu_capacities[node_idx]
        mem_cap = self.mem_capacities[node_idx]

        cpu_after = self.node_cpu_used[node_idx] + task["cpu_req"]
        mem_after = self.node_mem_used[node_idx] + task["mem_req"]

        # ── Check for overload ─────────────────────────────────
        cpu_overloaded = cpu_after > cpu_cap * 0.95
        mem_overloaded = mem_after > mem_cap * 0.95
        queue_full     = self.node_queue[node_idx] >= self.max_queue

        if cpu_overloaded or mem_overloaded or queue_full:
            # Soft overload: still assign but penalise heavily
            penalty = -2.0
            if cpu_overloaded: penalty -= 1.0
            if mem_overloaded: penalty -= 0.5
            if queue_full:     penalty -= 1.5
            latency = task["deadline"] * 1000 * 1.5  # late
            # Still update state
            self.node_cpu_used[node_idx]  = min(cpu_after, cpu_cap)
            self.node_mem_used[node_idx]  = min(mem_after, mem_cap)
            self.node_queue[node_idx]     = min(self.node_queue[node_idx] + 1, self.max_queue)
            return penalty, latency

        # ── Successful assignment ──────────────────────────────
        self.node_cpu_used[node_idx]  = cpu_after
        self.node_mem_used[node_idx]  = mem_after
        self.node_queue[node_idx]     += 1

        # Simulate task completion — partial queue drain per step
        drain = max(1, self.node_queue[node_idx] // 3)
        self.node_queue[node_idx]    = max(0, self.node_queue[node_idx] - drain)
        self.node_cpu_used[node_idx] = max(0.0, self.node_cpu_used[node_idx] - task["cpu_req"] * 0.4)
        self.node_mem_used[node_idx] = max(0.0, self.node_mem_used[node_idx] - task["mem_req"] * 0.3)

        # ── Latency model ──────────────────────────────────────
        cpu_util  = self.node_cpu_used[node_idx] / max(cpu_cap, 1.0)
        queue_len = self.node_queue[node_idx]
        base_latency = task["deadline"] * 1000 * 0.3   # 30 % of deadline in ms
        load_penalty = cpu_util * 200 + queue_len * 15
        latency      = base_latency + load_penalty
        latency      = float(np.clip(latency, 1.0, task["deadline"] * 1000))

        # ── Reward shaping ─────────────────────────────────────
        reward = 1.0  # base for successful assignment

        # Bonus: met deadline
        if latency < task["deadline"] * 1000:
            reward += 1.0

        # Bonus: priority urgency met fast
        priority_bonus = {0: 0.0, 1: 0.2, 2: 0.5, 3: 1.0}
        if latency < task["deadline"] * 500:    # met in < 50 % of deadline
            reward += priority_bonus.get(task["priority_num"], 0.0)

        # Penalty: high CPU utilisation
        if cpu_util > 0.8:
            reward -= (cpu_util - 0.8) * 3.0

        # Penalty: long queue
        if queue_len > self.max_queue * 0.6:
            reward -= 0.5

        # Bonus: load balancing (reward choosing less-loaded node)
        avg_cpu = np.mean(self.node_cpu_used) / max(np.mean(self.cpu_capacities), 1.0)
        this_cpu = self.node_cpu_used[node_idx] / max(cpu_cap, 1.0)
        if this_cpu < avg_cpu:
            reward += 0.3   # chose a less-loaded node — good

        return float(reward), latency

    def _delay_penalty(self, task: dict) -> float:
        """Penalty for choosing to delay a task."""
        priority_penalties = {
            "low":      -0.3,
            "medium":   -0.6,
            "high":     -1.2,
            "critical": -2.5,
        }
        return priority_penalties.get(task["priority"], -0.5)

    def _get_node_loads(self) -> list:
        """Return current load summary per node."""
        loads = []
        for i in range(self.num_nodes):
            loads.append({
                "node":        i,
                "cpu_used":    round(float(self.node_cpu_used[i]), 2),
                "mem_used":    round(float(self.node_mem_used[i]), 2),
                "queue":       int(self.node_queue[i]),
                "cpu_util_%":  round(float(self.node_cpu_used[i] / max(self.cpu_capacities[i], 1) * 100), 1),
            })
        return loads

    def get_episode_summary(self) -> dict:
        """Call after episode ends for full metrics."""
        if not self.episode_rewards:
            return {}

        rewards   = np.array(self.episode_rewards)
        latencies = np.array(self.episode_latencies)

        return {
            "total_reward":    round(float(rewards.sum()), 4),
            "mean_reward":     round(float(rewards.mean()), 4),
            "min_reward":      round(float(rewards.min()), 4),
            "max_reward":      round(float(rewards.max()), 4),
            "mean_latency_ms": round(float(latencies.mean()), 2),
            "min_latency_ms":  round(float(latencies.min()), 2),
            "max_latency_ms":  round(float(latencies.max()), 2),
            "steps":           self.current_step,
            "reward_history":  [round(float(r), 4) for r in rewards],
            "latency_history": [round(float(l), 2) for l in latencies],
            "allocation_log":  self.allocation_log,
        }
