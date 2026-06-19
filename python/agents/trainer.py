"""
AgentTrainer
============
Wraps Stable-Baselines3 PPO and DQN.
Handles training, model saving/loading, and inference.
All computation runs on CPU.
"""

import os
import json
import numpy as np
from typing import Optional, Callable

import torch
torch.set_num_threads(2)

from stable_baselines3 import PPO, DQN
from stable_baselines3.common.callbacks import BaseCallback

from environment.edge_computing_env import EdgeComputingEnv

MODELS_DIR = os.path.join(
    os.path.dirname(os.path.dirname(__file__)),
    "models", "saved"
)
os.makedirs(MODELS_DIR, exist_ok=True)


class ProgressCallback(BaseCallback):
    def __init__(self, total_timesteps: int, callback_fn: Callable, verbose=0):
        super().__init__(verbose)
        self.total_timesteps = total_timesteps
        self.callback_fn     = callback_fn

    def _on_step(self) -> bool:
        pct = int((self.num_timesteps / self.total_timesteps) * 100)
        self.callback_fn(min(pct, 99))
        return True


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

        policy_kwargs = dict(net_arch=[64, 64])

        if self.algorithm == "PPO":
            self.model = PPO(
                "MlpPolicy", self.env,
                learning_rate=3e-4, n_steps=256, batch_size=64,
                n_epochs=5, gamma=0.99, clip_range=0.2,
                policy_kwargs=policy_kwargs, device="cpu", verbose=0,
            )
        elif self.algorithm == "DQN":
            self.model = DQN(
                "MlpPolicy", self.env,
                learning_rate=1e-4, buffer_size=5000, learning_starts=500,
                batch_size=64, tau=0.005, gamma=0.99, train_freq=4,
                target_update_interval=500,
                policy_kwargs=policy_kwargs, device="cpu", verbose=0,
            )
        else:
            raise ValueError(f"Unsupported algorithm: {self.algorithm}")

        callbacks = []
        if progress_callback:
            callbacks.append(ProgressCallback(total_timesteps, progress_callback))

        self.model.learn(
            total_timesteps=total_timesteps,
            callback=callbacks if callbacks else None,
            progress_bar=False,
        )

        self.model.save(self.model_path.replace(".zip", ""))

        # ── Run evaluation episode ─────────────────────────────
        eval_summary = self._evaluate()

        # ── Persist node utilization to MySQL ──────────────────
        node_util = self._extract_node_utilization(eval_summary)
        self._persist_node_utilization(node_util)

        return {
            "algorithm":       self.algorithm,
            "total_timesteps": total_timesteps,
            "model_path":      self.model_path,
            "final_reward":    eval_summary.get("total_reward"),
            "mean_reward":     eval_summary.get("mean_reward"),
            "mean_latency_ms": eval_summary.get("mean_latency_ms"),
            "episodes":        1,
            "eval_summary":    eval_summary,
            "node_utilization": node_util,
        }

    def _evaluate(self) -> dict:
        """Run one greedy episode with the trained model."""
        obs, _ = self.env.reset(seed=0)
        done   = False

        while not done:
            action, _ = self.model.predict(obs, deterministic=True)
            obs, _, terminated, truncated, _ = self.env.step(int(action))
            done = terminated or truncated

        return self.env.get_episode_summary()

    def _extract_node_utilization(self, eval_summary: dict) -> list:
        """
        Build per-node utilization snapshot from env state
        after the evaluation episode.
        """
        nodes = []
        for i in range(self.env.num_nodes):
            cpu_cap = self.env.cpu_capacities[i]
            mem_cap = self.env.mem_capacities[i]
            cpu_used = float(self.env.node_cpu_used[i])
            mem_used = float(self.env.node_mem_used[i])
            queue    = int(self.env.node_queue[i])

            cpu_pct = round(min(cpu_used / max(cpu_cap, 1) * 100, 100), 2)
            mem_pct = round(min(mem_used / max(mem_cap, 1) * 100, 100), 2)

            # Determine status from utilization
            if cpu_pct >= 90 or mem_pct >= 90:
                status = "overloaded"
            elif cpu_pct >= 40 or queue > 3:
                status = "busy"
            elif cpu_pct > 0:
                status = "busy"
            else:
                status = "idle"

            nodes.append({
                "index":       i,
                "cpu_used":    cpu_used,
                "mem_used":    mem_used,
                "cpu_pct":     cpu_pct,
                "mem_pct":     mem_pct,
                "queue":       queue,
                "status":      status,
                "util_pct":    round((cpu_pct + mem_pct) / 2, 2),
            })

        return nodes

    def _persist_node_utilization(self, node_util: list) -> None:
        """Write node utilization back to MySQL edge_nodes table."""
        try:
            import mysql.connector
            import sys
            import os
            sys.path.insert(0, os.path.dirname(os.path.dirname(__file__)))
            from utils.config import db_config

            conn   = mysql.connector.connect(**db_config())
            cursor = conn.cursor(dictionary=True)

            # Get edge nodes for this simulation ordered by name
            cursor.execute(
                "SELECT id, name FROM edge_nodes WHERE simulation_id = %s ORDER BY name ASC",
                (self.simulation_id,)
            )
            db_nodes = cursor.fetchall()

            for i, db_node in enumerate(db_nodes):
                if i >= len(node_util):
                    break

                util = node_util[i]
                cursor.execute("""
                    UPDATE edge_nodes SET
                        cpu_used               = %s,
                        memory_used            = %s,
                        queue_length           = %s,
                        utilization_percentage = %s,
                        status                 = %s,
                        updated_at             = NOW()
                    WHERE id = %s
                """, (
                    round(util["cpu_used"], 2),
                    round(util["mem_used"], 2),
                    util["queue"],
                    util["util_pct"],
                    util["status"],
                    db_node["id"],
                ))

            conn.commit()
            cursor.close()
            conn.close()

        except Exception as e:
            # Non-fatal — training result is still saved even if node update fails
            print(f"[WARN] Could not persist node utilization: {e}", flush=True)

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
                if action < self.env.num_nodes
                else "DELAYED"
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

        summary = self.env.get_episode_summary()
        return {"allocations": allocations, "summary": summary}

    def _try_load_model(self) -> bool:
        if not os.path.exists(self.model_path):
            pattern = f"sim{self.simulation_id}_{self.algorithm.lower()}"
            for fname in os.listdir(MODELS_DIR):
                if fname.startswith(pattern) and fname.endswith(".zip"):
                    full_path = os.path.join(MODELS_DIR, fname)
                    try:
                        cls = PPO if self.algorithm == "PPO" else DQN
                        self.model = cls.load(full_path, env=self.env, device="cpu")
                        return True
                    except Exception:
                        continue
            return False

        try:
            cls = PPO if self.algorithm == "PPO" else DQN
            self.model = cls.load(
                self.model_path.replace(".zip", ""),
                env=self.env, device="cpu",
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