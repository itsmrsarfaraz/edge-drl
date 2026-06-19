"""Quick smoke test for EdgeComputingEnv."""

import sys
import os
sys.path.insert(0, os.path.dirname(os.path.dirname(__file__)))

from environment.edge_computing_env import EdgeComputingEnv

def test_env():
    print("=" * 50)
    print("EdgeComputingEnv Smoke Test")
    print("=" * 50)

    env = EdgeComputingEnv(num_nodes=3, max_steps=20)

    # Test reset
    obs, info = env.reset(seed=42)
    print(f"\n✔ Reset OK")
    print(f"   Obs shape : {obs.shape}  (expected: ({env.num_nodes * 3 + 4},))")
    print(f"   Obs range : [{obs.min():.3f}, {obs.max():.3f}]  (expected: [0, 1])")
    print(f"   Action space: {env.action_space}  (0-{env.num_nodes-1}=assign, {env.num_nodes}=delay)")

    # Run one episode with random actions
    total_reward = 0.0
    for step in range(20):
        action = env.action_space.sample()
        obs, reward, terminated, truncated, info = env.step(action)
        total_reward += reward
        if terminated:
            break

    summary = env.get_episode_summary()
    print(f"\n✔ Episode completed")
    print(f"   Steps        : {summary['steps']}")
    print(f"   Total reward : {summary['total_reward']}")
    print(f"   Mean reward  : {summary['mean_reward']}")
    print(f"   Mean latency : {summary['mean_latency_ms']} ms")

    # Test action space
    for action in range(env.num_nodes + 1):
        assert env.action_space.contains(action), f"Action {action} not in space"
    print(f"\n✔ All {env.num_nodes + 1} actions valid")

    env.close()
    print("\n✔ All tests passed.\n")

if __name__ == "__main__":
    test_env()
