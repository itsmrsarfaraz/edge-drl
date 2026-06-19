#!/bin/bash
# Start the Edge DRL FastAPI server
# Run from: ~/projects/edge-drl/python/

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PYTHON_DIR="$(dirname "$SCRIPT_DIR")"
VENV_PYTHON="$PYTHON_DIR/venv/bin/python3"

# Load Laravel .env for DB credentials
LARAVEL_ENV="$(dirname "$PYTHON_DIR")/.env"
if [ -f "$LARAVEL_ENV" ]; then
    export $(grep -v '^#' "$LARAVEL_ENV" | grep -E '^(DB_|APP_)' | xargs)
fi

echo "Starting Edge DRL FastAPI on http://127.0.0.1:8001"
echo "Docs: http://127.0.0.1:8001/docs"
echo ""

cd "$PYTHON_DIR"
"$VENV_PYTHON" -m uvicorn api.main:app --host 127.0.0.1 --port 8001 --reload
