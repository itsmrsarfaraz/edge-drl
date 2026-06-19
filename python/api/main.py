"""
Edge DRL — FastAPI AI Engine
============================
Provides HTTP endpoints for Laravel to:
  - Check health
  - Run training (PPO / DQN)
  - Run inference (task allocation)
  - Fetch training status
"""

import os
import sys
import json
import uuid
import asyncio
import threading
from datetime import datetime, timezone
from typing import Optional

sys.path.insert(0, os.path.dirname(os.path.dirname(__file__)))

from fastapi import FastAPI, HTTPException, BackgroundTasks
from fastapi.middleware.cors import CORSMiddleware
from pydantic import BaseModel, Field

from environment.edge_computing_env import EdgeComputingEnv
from agents.trainer import AgentTrainer

# ── App ────────────────────────────────────────────────────────
app = FastAPI(
    title       = "Edge DRL AI Engine",
    description = "Deep Reinforcement Learning API for Edge Computing Resource Allocation",
    version     = "1.0.0",
)

app.add_middleware(
    CORSMiddleware,
    allow_origins  = ["http://localhost:8000", "http://127.0.0.1:8000"],
    allow_methods  = ["*"],
    allow_headers  = ["*"],
)

# ── In-memory job store (keyed by training_run_id) ─────────────
# Each entry: {status, progress, result, error, started_at}
_jobs: dict = {}
_jobs_lock  = threading.Lock()


# ── Request / Response Models ──────────────────────────────────

class TrainRequest(BaseModel):
    simulation_id:   int
    training_run_id: int
    algorithm:       str   = Field("PPO", pattern="^(PPO|DQN)$")
    num_nodes:       int   = Field(3,   ge=1, le=10)
    num_tasks:       int   = Field(50,  ge=10, le=500)
    total_timesteps: int   = Field(10000, ge=1000, le=100000)
    cpu_capacities:  Optional[list[float]] = None
    mem_capacities:  Optional[list[float]] = None


class InferRequest(BaseModel):
    simulation_id: int
    algorithm:     str   = Field("PPO", pattern="^(PPO|DQN)$")
    num_nodes:     int   = Field(3, ge=1, le=10)
    tasks:         list[dict]
    cpu_capacities: Optional[list[float]] = None
    mem_capacities: Optional[list[float]] = None


# ── Routes ─────────────────────────────────────────────────────

@app.get("/health")
def health():
    return {
        "status":    "ok",
        "service":   "Edge DRL AI Engine",
        "timestamp": datetime.now(timezone.utc).isoformat(),
    }


@app.get("/")
def root():
    return {"message": "Edge DRL API is running. Visit /docs for API docs."}


@app.post("/train")
def start_training(req: TrainRequest, background_tasks: BackgroundTasks):
    """
    Start a training job in the background.
    Returns immediately with a job_id (= training_run_id).
    Laravel polls /train/{training_run_id}/status.
    """
    job_id = str(req.training_run_id)

    with _jobs_lock:
        if job_id in _jobs and _jobs[job_id]["status"] == "running":
            raise HTTPException(409, "Training job already running for this run ID.")

        _jobs[job_id] = {
            "status":     "running",
            "progress":   0,
            "result":     None,
            "error":      None,
            "started_at": datetime.now(timezone.utc).isoformat(),
        }

    background_tasks.add_task(_run_training, req, job_id)

    return {
        "job_id":    job_id,
        "status":    "running",
        "message":   f"Training started ({req.algorithm}, {req.total_timesteps} timesteps)",
    }


@app.get("/train/{training_run_id}/status")
def training_status(training_run_id: int):
    """Poll this endpoint to check training progress."""
    job_id = str(training_run_id)
    with _jobs_lock:
        job = _jobs.get(job_id)

    if not job:
        raise HTTPException(404, "Training job not found.")

    return {
        "job_id":     job_id,
        "status":     job["status"],
        "progress":   job["progress"],
        "result":     job["result"],
        "error":      job["error"],
        "started_at": job["started_at"],
    }


@app.post("/infer")
def run_inference(req: InferRequest):
    """
    Run inference: use a trained model to allocate a list of tasks.
    If no trained model exists, falls back to a greedy heuristic.
    """
    env = EdgeComputingEnv(
        num_nodes      = req.num_nodes,
        max_steps      = len(req.tasks),
        cpu_capacities = req.cpu_capacities,
        mem_capacities = req.mem_capacities,
    )

    trainer = AgentTrainer(
        env            = env,
        algorithm      = req.algorithm,
        simulation_id  = req.simulation_id,
    )

    results = trainer.infer(req.tasks)
    env.close()

    return {
        "simulation_id": req.simulation_id,
        "algorithm":     req.algorithm,
        "allocations":   results["allocations"],
        "summary":       results["summary"],
    }


@app.get("/env/info")
def env_info():
    """Return observation/action space info for a default 3-node environment."""
    env = EdgeComputingEnv(num_nodes=3)
    info = {
        "observation_space": {
            "shape": list(env.observation_space.shape),
            "low":   [float(x) for x in env.observation_space.low],
            "high":  [float(x) for x in env.observation_space.high],
        },
        "action_space": {
            "n":    int(env.action_space.n),   # ← cast numpy.int64 to Python int
            "type": "Discrete",
        },
        "description": {
            "observation": "Per-node: [cpu_util, mem_util, queue_ratio] + task: [cpu_req, mem_req, priority, urgency]",
            "actions":     "0..num_nodes-1 = assign to node, num_nodes = delay task",
        },
    }
    env.close()
    return info


# ── Background job runner ──────────────────────────────────────

def _run_training(req: TrainRequest, job_id: str):
    """Runs in a background thread. Updates _jobs in place."""
    try:
        env = EdgeComputingEnv(
            num_nodes      = req.num_nodes,
            max_steps      = req.num_tasks,
            cpu_capacities = req.cpu_capacities,
            mem_capacities = req.mem_capacities,
        )

        trainer = AgentTrainer(
            env            = env,
            algorithm      = req.algorithm,
            simulation_id  = req.simulation_id,
            training_run_id= req.training_run_id,
        )

        def progress_callback(progress_pct: int):
            with _jobs_lock:
                if job_id in _jobs:
                    _jobs[job_id]["progress"] = progress_pct

        result = trainer.train(
            total_timesteps   = req.total_timesteps,
            progress_callback = progress_callback,
        )

        with _jobs_lock:
            _jobs[job_id]["status"]   = "completed"
            _jobs[job_id]["progress"] = 100
            _jobs[job_id]["result"]   = result

    except Exception as e:
        with _jobs_lock:
            _jobs[job_id]["status"] = "failed"
            _jobs[job_id]["error"]  = str(e)
