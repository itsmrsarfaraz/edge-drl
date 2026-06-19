"""
AgentTrainer
============
Wraps Stable-Baselines3 PPO and DQN.
Handles training, model saving/loading, and inference.
All computation runs on CPU.
Enhanced with Training Curve + Evaluation Metrics
"""

import os
import json
import numpy as np
from typing import Optional, Callable

import torch
torch.set_num_threads(2)

from stable_baselines3 import PPO, DQN
from stable_baselines3.common.callbacks import BaseCallback, EvalCallback
from stable_baselines3.common.monitor import Monitor
from stable_baselines3.common.evaluation import evaluate_policy

from environment.edge_computing_env import EdgeComputingEnv

MODELS_DIR = os.path.join(
    os.path.dirname(os.path.dirname(__file__)),
    "models", "saved"
)
os.makedirs(MODELS_DIR, exist_ok=True)


# ── Callback: records mean reward every N timesteps ───────────
class TrainingCurveCallback(BaseCallback):
    """
    Records mean episode reward at regular checkpoints during training.
    This gives us the learning curve (training accuracy equivalent).
    """

    def __init__(self, total_timesteps: int, num_checkpoints: int = 20,
                 progress_callback: Optional[Callable] = None, verbose=0):
        super().__init__(verbose)
        self.total_timesteps  = total_timesteps
        self.checkpoint_every = max(total_timesteps // num_checkpoints, 1)
        self.progress_callback = progress_callback

        # Recorded every checkpoint
        self.curve_timesteps   = []   # x-axis: timestep number
        self.curve_mean_reward = []   # y-axis: mean reward
        self.curve_std_reward  = []   # y-axis: std of reward
        self._episode_rewards  = []   # buffer for current window
        self._last_checkpoint  = 0

    def _on_step(self) -> bool:
        # Collect episode rewards from the Monitor wrapper
        if self.locals.get("dones") is not None:
            for done, info in zip(
                self.locals["dones"],
                self.locals.get("infos", [{}])
            ):
                if done and "episode" in info:
                    self._episode_rewards.append(info["episode"]["r"])

        # Report progress
        if self.progress_callback:
            pct = int((self.num_timesteps / self.total_timesteps) * 100)
            self.progress_callback(min(pct, 99))

        # Checkpoint: record curve point
        if self.num_timesteps - self._last_checkpoint >= self.checkpoint_every:
            if self._episode_rewards:
                mean_r = float(np.mean(self._episode_rewards[-20:]))
                std_r  = float(np.std(self._episode_rewards[-20:]))
            else:
                mean_r = 0.0
                std_r  = 0.0

            self.curve_timesteps.append(self.num_timesteps)
            self.curve_mean_reward.append(round(mean_r, 4))
            self.curve_std_reward.append(round(std_r, 4))
            self._last_checkpoint = self.num_timesteps

        return True

    def get_curve(self) -> dict:
        return {
            "timesteps":   self.curve_timesteps,
            "mean_reward": self.curve_mean_reward,
            "std_reward":  self.curve_std_reward,
        }


class AgentTrainer:
    def __init__(
        self,
        env,
        algorithm:       str = "PPO",
        simulation_id:   int = 0,
        training_run_id: int = 0,
    ):
        self.env             = env
        self.algorithm       = algorithm.upper()
        self.simulation_id   = simulation_id
        self.training_run_id = training_run_id
        self.model           = None

        self.model_path = os.path.join(
            MODELS_DIR,
            f"sim{simulation_id}_{algorithm.lower()}_run{training_run_id}.zip"
        )

    def train(
        self,
        total_timesteps:   int = 10000,
        progress_callback: Optional[Callable] = None,
    ) -> dict:

        # Wrap env with Monitor to capture episode rewards
        monitored_env = Monitor(self.env)

        policy_kwargs = dict(net_arch=[64, 64])

        if self.algorithm == "PPO":
            self.model = PPO(
                "MlpPolicy", monitored_env,
                learning_rate=3e-4, n_steps=256, batch_size=64,
                n_epochs=5, gamma=0.99, clip_range=0.2,
                policy_kwargs=policy_kwargs, device="cpu", verbose=0,
            )
        elif self.algorithm == "DQN":
            self.model = DQN(
                "MlpPolicy", monitored_env,
                learning_rate=1e-4, buffer_size=5000, learning_starts=500,
                batch_size=64, tau=0.005, gamma=0.99, train_freq=4,
                target_update_interval=500,
                policy_kwargs=policy_kwargs, device="cpu", verbose=0,
            )
        else:
            raise ValueError(f"Unsupported algorithm: {self.algorithm}")

        # ── Training curve callback ────────────────────────────
        curve_cb = TrainingCurveCallback(
            total_timesteps  = total_timesteps,
            num_checkpoints  = 20,
            progress_callback= progress_callback,
        )

        self.model.learn(
            total_timesteps = total_timesteps,
            callback        = curve_cb,
            progress_bar    = False,
        )

        self.model.save(self.model_path.replace(".zip", ""))

        # ── Training curve data ────────────────────────────────
        training_curve = curve_cb.get_curve()

        # ── Evaluation: multiple episodes (test performance) ───
        eval_results = self._evaluate_multiple(n_episodes=10)

        # ── Single eval episode for step-by-step logs ─────────
        eval_summary = self._evaluate_single()

        # ── Persist node utilization ───────────────────────────
        node_util = self._extract_node_utilization()
        self._persist_node_utilization(node_util)

        return {
            "algorithm":        self.algorithm,
            "total_timesteps":  total_timesteps,
            "model_path":       self.model_path,

            # Training performance (learning curve)
            "training_curve":   training_curve,
            "train_mean_reward":round(float(np.mean(training_curve["mean_reward"][-5:])), 4)
                                if training_curve["mean_reward"] else None,

            # Evaluation performance (test accuracy equivalent)
            "eval_mean_reward": eval_results["mean_reward"],
            "eval_std_reward":  eval_results["std_reward"],
            "eval_min_reward":  eval_results["min_reward"],
            "eval_max_reward":  eval_results["max_reward"],
            "eval_episodes":    eval_results["n_episodes"],
            "eval_success_rate":eval_results["success_rate"],

            # Backward compat fields
            "final_reward":     eval_summary.get("total_reward"),
            "mean_reward":      eval_results["mean_reward"],
            "mean_latency_ms":  eval_summary.get("mean_latency_ms"),
            "episodes":         eval_results["n_episodes"],
            "eval_summary":     eval_summary,
            "node_utilization": node_util,
        }

    def _evaluate_multiple(self, n_episodes: int = 10) -> dict:
        """
        Run N independent evaluation episodes with the trained model.
        This is the 'test accuracy' equivalent — measures generalization.
        """
        episode_rewards  = []
        episode_lengths  = []
        positive_steps   = 0
        total_steps      = 0

        for ep in range(n_episodes):
            obs, _ = self.env.reset(seed=ep * 42)
            done   = False
            ep_reward = 0.0
            ep_steps  = 0

            while not done:
                action, _ = self.model.predict(obs, deterministic=True)
                obs, reward, terminated, truncated, _ = self.env.step(int(action))
                ep_reward   += reward
                ep_steps    += 1
                total_steps += 1
                if reward > 0:
                    positive_steps += 1
                done = terminated or truncated

            episode_rewards.append(ep_reward)
            episode_lengths.append(ep_steps)

        rewards = np.array(episode_rewards)

        return {
            "n_episodes":   n_episodes,
            "mean_reward":  round(float(rewards.mean()), 4),
            "std_reward":   round(float(rewards.std()),  4),
            "min_reward":   round(float(rewards.min()),  4),
            "max_reward":   round(float(rewards.max()),  4),
            "mean_length":  round(float(np.mean(episode_lengths)), 1),
            "success_rate": round(positive_steps / max(total_steps, 1) * 100, 2),
            "all_rewards":  [round(float(r), 4) for r in episode_rewards],
        }

    def _evaluate_single(self) -> dict:
        """Single deterministic episode for step-level logs."""
        obs, _ = self.env.reset(seed=0)
        done   = False

        while not done:
            action, _ = self.model.predict(obs, deterministic=True)
            obs, _, terminated, truncated, _ = self.env.step(int(action))
            done = terminated or truncated

        return self.env.get_episode_summary()

    def _extract_node_utilization(self) -> list:
        nodes = []
        for i in range(self.env.num_nodes):
            cpu_cap  = self.env.cpu_capacities[i]
            mem_cap  = self.env.mem_capacities[i]
            cpu_used = float(self.env.node_cpu_used[i])
            mem_used = float(self.env.node_mem_used[i])
            queue    = int(self.env.node_queue[i])
            cpu_pct  = round(min(cpu_used / max(cpu_cap, 1) * 100, 100), 2)
            mem_pct  = round(min(mem_used / max(mem_cap, 1) * 100, 100), 2)

            if cpu_pct >= 90 or mem_pct >= 90:
                status = "overloaded"
            elif cpu_pct >= 40 or queue > 3:
                status = "busy"
            elif cpu_pct > 0:
                status = "busy"
            else:
                status = "idle"

            nodes.append({
                "index":    i,
                "cpu_used": cpu_used, "mem_used": mem_used,
                "cpu_pct":  cpu_pct,  "mem_pct":  mem_pct,
                "queue":    queue,    "status":   status,
                "util_pct": round((cpu_pct + mem_pct) / 2, 2),
            })
        return nodes

    def _persist_node_utilization(self, node_util: list) -> None:
        try:
            import mysql.connector
            import sys, os
            sys.path.insert(0, os.path.dirname(os.path.dirname(__file__)))
            from utils.config import db_config

            conn   = mysql.connector.connect(**db_config())
            cursor = conn.cursor(dictionary=True)
            cursor.execute(
                "SELECT id FROM edge_nodes WHERE simulation_id = %s ORDER BY name ASC",
                (self.simulation_id,)
            )
            db_nodes = cursor.fetchall()

            for i, db_node in enumerate(db_nodes):
                if i >= len(node_util):
                    break
                util = node_util[i]
                cursor.execute("""
                    UPDATE edge_nodes SET
                        cpu_used=%s, memory_used=%s, queue_length=%s,
                        utilization_percentage=%s, status=%s, updated_at=NOW()
                    WHERE id=%s
                """, (
                    round(util["cpu_used"], 2), round(util["mem_used"], 2),
                    util["queue"], util["util_pct"], util["status"], db_node["id"],
                ))

            conn.commit()
            cursor.close()
            conn.close()
        except Exception as e:
            print(f"[WARN] Node persist failed: {e}", flush=True)

    def infer(self, tasks: list) -> dict:
        model_loaded = self._try_load_model()
        obs, _ = self.env.reset(seed=42)
        allocations = []

        for i, task in enumerate(tasks):
            if model_loaded:
                action, _ = self.model.predict(obs, deterministic=True)
                action = int(action)
            else:
                action = self._greedy_action()

            obs, reward, terminated, truncated, info = self.env.step(action)
            node_name = (
                f"Edge-Node-{str(action + 1).zfill(2)}"
                if action < self.env.num_nodes else "DELAYED"
            )
            allocations.append({
                "task_index":    i,
                "task_label":    task.get("task_id_label", f"TASK-{str(i+1).zfill(4)}"),
                "action":        action,
                "node_assigned": node_name,
                "reward":        round(float(reward), 4),
                "latency_ms":    round(float(info.get("latency", 0)), 2),
                "status":        "delayed" if action >= self.env.num_nodes else "completed",
            })
            if terminated or truncated:
                break

        return {"allocations": allocations, "summary": self.env.get_episode_summary()}

    def _try_load_model(self) -> bool:
        if not os.path.exists(self.model_path):
            pattern = f"sim{self.simulation_id}_{self.algorithm.lower()}"
            for fname in os.listdir(MODELS_DIR):
                if fname.startswith(pattern) and fname.endswith(".zip"):
                    try:
                        cls = PPO if self.algorithm == "PPO" else DQN
                        self.model = cls.load(
                            os.path.join(MODELS_DIR, fname),
                            env=self.env, device="cpu"
                        )
                        return True
                    except Exception:
                        continue
            return False
        try:
            cls = PPO if self.algorithm == "PPO" else DQN
            self.model = cls.load(
                self.model_path.replace(".zip", ""),
                env=self.env, device="cpu"
            )
            return True
        except Exception:
            return False

    def _greedy_action(self) -> int:
        if not hasattr(self.env, 'node_cpu_used'):
            return 0
        loads = [
            self.env.node_cpu_used[i] / max(self.env.cpu_capacities[i], 1.0)
            for i in range(self.env.num_nodes)
        ]
        return int(np.argmin(loads))