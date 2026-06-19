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
torch.set_num_threads(2)   # limit CPU threads — polite on a shared laptop

from stable_baselines3 import PPO, DQN
from stable_baselines3.common.callbacks import BaseCallback
from stable_baselines3.common.env_util import make_vec_env

from environment.edge_computing_env import EdgeComputingEnv


# ── Model storage path ─────────────────────────────────────────
MODELS_DIR = os.path.join(
    os.path.dirname(os.path.dirname(__file__)),
    "models", "saved"
)
os.makedirs(MODELS_DIR, exist_ok=True)


class ProgressCallback(BaseCallback):
    """Reports training progress % back to FastAPI via callback."""

    def __init__(self, total_timesteps: int, callback_fn: Callable, verbose=0):
        super().__init__(verbose)
        self.total_timesteps = total_timesteps
        self.callback_fn     = callback_fn

    def _on_step(self) -> bool:
        pct = int((self.num_timesteps / self.total_timesteps) * 100)
        self.callback_fn(min(pct, 99))
        return True


class AgentTrainer:
    """
    Manages PPO / DQN training and inference for a simulation.
    """

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
        """
        Train the agent. Returns a result dict with metrics.
        Uses small networks to stay fast on CPU.
        """

        # ── Small network policy for CPU speed ────────────────
        policy_kwargs = dict(
            net_arch = [64, 64],   # 2 hidden layers of 64 units
        )

        if self.algorithm == "PPO":
            self.model = PPO(
                "MlpPolicy",
                self.env,
                learning_rate  = 3e-4,
                n_steps        = 256,
                batch_size     = 64,
                n_epochs       = 5,
                gamma          = 0.99,
                clip_range     = 0.2,
                policy_kwargs  = policy_kwargs,
                device         = "cpu",
                verbose        = 0,
            )
        elif self.algorithm == "DQN":
            self.model = DQN(
                "MlpPolicy",
                self.env,
                learning_rate         = 1e-4,
                buffer_size           = 5000,
                learning_starts       = 500,
                batch_size            = 64,
                tau                   = 0.005,
                gamma                 = 0.99,
                train_freq            = 4,
                target_update_interval= 500,
                policy_kwargs         = policy_kwargs,
                device                = "cpu",
                verbose               = 0,
            )
        else:
            raise ValueError(f"Unsupported algorithm: {self.algorithm}")

        # ── Callbacks ──────────────────────────────────────────
        callbacks = []
        if progress_callback:
            callbacks.append(
                ProgressCallback(total_timesteps, progress_callback)
            )

        # ── Train ──────────────────────────────────────────────
        self.model.learn(
            total_timesteps = total_timesteps,
            callback        = callbacks if callbacks else None,
            progress_bar    = False,
        )

        # ── Save model ─────────────────────────────────────────
        self.model.save(self.model_path.replace(".zip", ""))

        # ── Evaluate: run one episode with trained model ───────
        eval_summary = self._evaluate()

        return {
            "algorithm":       self.algorithm,
            "total_timesteps": total_timesteps,
            "model_path":      self.model_path,
            "final_reward":    eval_summary.get("total_reward"),
            "mean_reward":     eval_summary.get("mean_reward"),
            "mean_latency_ms": eval_summary.get("mean_latency_ms"),
            "episodes":        1,
            "eval_summary":    eval_summary,
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

    def infer(self, tasks: list) -> dict:
        """
        Allocate a list of tasks using a trained model (or greedy fallback).
        Returns allocations + summary metrics.
        """
        # Try loading saved model
        model_loaded = self._try_load_model()

        obs, _ = self.env.reset(seed=42)
        allocations = []

        for i, task in enumerate(tasks):
            if model_loaded:
                action, _ = self.model.predict(obs, deterministic=True)
                action = int(action)
            else:
                # Greedy fallback: pick least loaded node
                action = self._greedy_action()

            obs, reward, terminated, truncated, info = self.env.step(action)

            node_name = f"Edge-Node-{str(action + 1).zfill(2)}" if action < self.env.num_nodes else "DELAYED"

            allocations.append({
                "task_index":   i,
                "task_label":   task.get("task_id_label", f"TASK-{str(i+1).zfill(4)}"),
                "action":       action,
                "node_assigned": node_name,
                "reward":       round(float(reward), 4),
                "latency_ms":   round(float(info.get("latency", 0)), 2),
                "status":       "delayed" if action >= self.env.num_nodes else "completed",
            })

            if terminated or truncated:
                break

        summary = self.env.get_episode_summary()
        return {"allocations": allocations, "summary": summary}

    def _try_load_model(self) -> bool:
        """Try to load a saved model. Returns True if successful."""
        if not os.path.exists(self.model_path):
            # Try any model for this simulation
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
                env    = self.env,
                device = "cpu",
            )
            return True
        except Exception:
            return False

    def _greedy_action(self) -> int:
        """Greedy fallback: choose node with lowest CPU utilisation."""
        if not hasattr(self.env, 'node_cpu_used'):
            return 0
        loads = [
            self.env.node_cpu_used[i] / max(self.env.cpu_capacities[i], 1.0)
            for i in range(self.env.num_nodes)
        ]
        return int(np.argmin(loads))
