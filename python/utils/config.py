"""
Central config loader for the Python AI engine.
Reads from environment variables set by Laravel or a local .env file.
"""

import os


def db_config() -> dict:
    return {
        "host":     os.getenv("DB_HOST",     "127.0.0.1"),
        "port":     int(os.getenv("DB_PORT", "3306")),
        "user":     os.getenv("DB_USERNAME", "root"),
        "password": os.getenv("DB_PASSWORD", ""),
        "database": os.getenv("DB_DATABASE", "edge_drl"),
    }


def python_env() -> str:
    """Return current environment name."""
    return os.getenv("APP_ENV", "local")
