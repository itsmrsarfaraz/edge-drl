#!/usr/bin/env python3
"""
Task Generator for Edge DRL Simulation Platform
Generates realistic IoT task workloads and writes them to MySQL.

Usage:
    python3 generate_tasks.py --simulation_id=1 --num_tasks=50
"""

import argparse
import json
import random
import sys
import os
from datetime import datetime, timezone

import numpy as np

# ── MySQL connector (pure Python, no C extensions needed) ──────
try:
    import mysql.connector
except ImportError:
    print(json.dumps({"error": "mysql-connector-python not installed. Run: pip3 install mysql-connector-python"}))
    sys.exit(1)

# ── Config ─────────────────────────────────────────────────────
DB_CONFIG = {
    "host":     os.getenv("DB_HOST",     "127.0.0.1"),
    "port":     int(os.getenv("DB_PORT", "3306")),
    "user":     os.getenv("DB_USERNAME", "root"),
    "password": os.getenv("DB_PASSWORD", ""),
    "database": os.getenv("DB_DATABASE", "edge_drl"),
}

# ── Priority distribution (weighted) ───────────────────────────
PRIORITY_WEIGHTS = {
    "low":      0.25,
    "medium":   0.45,
    "high":     0.20,
    "critical": 0.10,
}

# ── Per-priority resource profiles ─────────────────────────────
TASK_PROFILES = {
    "low": {
        "cpu_req":    (2.0,  12.0),   # % of node CPU
        "mem_req":    (64,   256),    # MB
        "task_size":  (0.1,  1.0),    # MB
        "deadline":   (8.0,  20.0),   # seconds
    },
    "medium": {
        "cpu_req":    (5.0,  25.0),
        "mem_req":    (128,  512),
        "task_size":  (0.5,  5.0),
        "deadline":   (4.0,  10.0),
    },
    "high": {
        "cpu_req":    (15.0, 45.0),
        "mem_req":    (256,  1024),
        "task_size":  (2.0,  20.0),
        "deadline":   (2.0,  6.0),
    },
    "critical": {
        "cpu_req":    (30.0, 70.0),
        "mem_req":    (512,  2048),
        "task_size":  (5.0,  50.0),
        "deadline":   (0.5,  3.0),
    },
}

# ── Device type → typical task type mapping ────────────────────
DEVICE_TASK_BIAS = {
    "temperature_sensor": "low",
    "motion_sensor":      "medium",
    "camera":             "high",
    "actuator":           "medium",
    "gateway":            "critical",
}


def pick_priority(device_type: str) -> str:
    """
    Bias task priority toward the device type's natural workload,
    but still allow some randomness.
    """
    base = DEVICE_TASK_BIAS.get(device_type, "medium")
    priorities = list(PRIORITY_WEIGHTS.keys())
    weights    = list(PRIORITY_WEIGHTS.values())

    # Boost the base priority weight by 40 %
    idx = priorities.index(base)
    boosted = [w * 1.4 if i == idx else w for i, w in enumerate(weights)]
    total = sum(boosted)
    boosted = [w / total for w in boosted]

    return random.choices(priorities, weights=boosted, k=1)[0]


def generate_task(task_index: int, simulation_id: int, devices: list) -> dict:
    device    = random.choice(devices)
    priority  = pick_priority(device["type"])
    profile   = TASK_PROFILES[priority]

    cpu_req   = round(random.uniform(*profile["cpu_req"]),  2)
    mem_req   = round(random.uniform(*profile["mem_req"]),  1)
    task_size = round(random.uniform(*profile["task_size"]), 3)
    deadline  = round(random.uniform(*profile["deadline"]),  2)

    # Add slight Gaussian noise for realism
    cpu_req   = max(0.5, round(cpu_req   + np.random.normal(0, 0.5), 2))
    mem_req   = max(32,  round(mem_req   + np.random.normal(0, 10),  1))

    return {
        "simulation_id":     simulation_id,
        "iot_device_id":     device["id"],
        "task_id_label":     f"TASK-{str(task_index + 1).zfill(4)}",
        "priority":          priority,
        "cpu_requirement":   cpu_req,
        "memory_requirement": mem_req,
        "task_size":         task_size,
        "deadline":          deadline,
        "status":            "pending",
        "generated_at":      datetime.now(timezone.utc).strftime("%Y-%m-%d %H:%M:%S"),
    }


def main():
    parser = argparse.ArgumentParser(description="Generate IoT tasks for Edge DRL simulation")
    parser.add_argument("--simulation_id", type=int, required=True)
    parser.add_argument("--num_tasks",     type=int, default=50)
    args = parser.parse_args()

    # ── Connect ────────────────────────────────────────────────
    try:
        conn   = mysql.connector.connect(**DB_CONFIG)
        cursor = conn.cursor(dictionary=True)
    except mysql.connector.Error as e:
        print(json.dumps({"error": f"DB connection failed: {e}"}))
        sys.exit(1)

    # ── Fetch devices for this simulation ─────────────────────
    cursor.execute(
        "SELECT id, type FROM iot_devices WHERE simulation_id = %s AND is_active = 1",
        (args.simulation_id,)
    )
    devices = cursor.fetchall()

    if not devices:
        print(json.dumps({"error": "No active IoT devices found for this simulation."}))
        cursor.close()
        conn.close()
        sys.exit(1)

    # ── Clear old pending tasks ────────────────────────────────
    cursor.execute(
        "DELETE FROM tasks WHERE simulation_id = %s AND status = 'pending'",
        (args.simulation_id,)
    )

    # ── Generate & insert ──────────────────────────────────────
    insert_sql = """
        INSERT INTO tasks
            (simulation_id, iot_device_id, task_id_label, priority,
             cpu_requirement, memory_requirement, task_size, deadline,
             status, generated_at, created_at, updated_at)
        VALUES
            (%(simulation_id)s, %(iot_device_id)s, %(task_id_label)s, %(priority)s,
             %(cpu_requirement)s, %(memory_requirement)s, %(task_size)s, %(deadline)s,
             %(status)s, %(generated_at)s, NOW(), NOW())
    """

    now = datetime.now(timezone.utc).strftime("%Y-%m-%d %H:%M:%S")
    tasks_data = []
    priority_counts = {"low": 0, "medium": 0, "high": 0, "critical": 0}

    for i in range(args.num_tasks):
        task = generate_task(i, args.simulation_id, devices)
        tasks_data.append(task)
        priority_counts[task["priority"]] += 1
        cursor.execute(insert_sql, task)

    conn.commit()

    # ── Summary ────────────────────────────────────────────────
    result = {
        "success":          True,
        "simulation_id":    args.simulation_id,
        "tasks_generated":  args.num_tasks,
        "priority_breakdown": priority_counts,
        "sample": {
            "first": tasks_data[0]  if tasks_data else None,
            "last":  tasks_data[-1] if tasks_data else None,
        }
    }

    print(json.dumps(result))
    cursor.close()
    conn.close()


if __name__ == "__main__":
    main()
