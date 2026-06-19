cat > README.md << 'MD'
# Edge DRL — Resource Allocation in Edge Computing using Deep Reinforcement Learning

A web-based simulation platform for Final Year Project (FYP) demonstrating how Deep Reinforcement Learning can perform resource allocation in edge computing environments. Runs entirely locally — no cloud, no GPU, no IoT hardware required.

---

## Tech Stack

| Layer     | Technology                                |
|-----------|-------------------------------------------|
| Frontend  | Laravel 13 · Blade · Tailwind CSS v4 · Alpine.js · Chart.js |
| Backend   | Laravel 13 · PHP 8.5 · MySQL             |
| AI Engine | Python 3.14 · FastAPI · Stable-Baselines3 · Gymnasium · PyTorch CPU |
| Dev Env   | WSL Ubuntu · No Docker required           |

---

## Requirements

- WSL Ubuntu
- PHP 8.5
- Composer 2.x
- Node.js 18+ & npm
- MySQL 8.x
- Python 3.10+

---

## Setup

### 1. Clone & install

```bash
git clone https://github.com/itsmrsarfaraz/edge-drl.git
cd edge-drl
composer install
npm install
cp .env.example .env
php artisan key:generate
```

### 2. Configure `.env`

```env
DB_DATABASE=edge_drl
DB_USERNAME=root
DB_PASSWORD=your_password
PYTHON_PATH=/home/yourname/projects/edge-drl/python/venv/bin/python3
```

### 3. Database

```bash
mysql -u root -p -e "CREATE DATABASE edge_drl CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
php artisan migrate
```

### 4. Python environment

```bash
cd python
python3 -m venv venv
source venv/bin/activate
pip install -r requirements.txt
```

### 5. Build frontend

```bash
cd ..
npm run build
```

---

## Running the Project

Open **two terminals**:

**Terminal 1 — Laravel:**
```bash
php artisan serve
# → http://localhost:8000
```

**Terminal 2 — FastAPI AI Engine:**
```bash
cd python
source venv/bin/activate
bash api/start.sh
# → http://127.0.0.1:8001
```

---

## Project Modules

| Module | Description |
|--------|-------------|
| Authentication | Login · Register · Profile |
| Simulations | Create · Configure · Manage |
| Edge Nodes | Auto-provisioned · Resource bars · Status |
| IoT Tasks | Python-generated · Weighted priorities |
| DRL Training | PPO & DQN via Stable-Baselines3 · Live progress |
| Analytics | 5 Chart.js charts · Reward/latency/utilization |
| Reports | Downloadable PDF · PPO vs DQN comparison |

---

## Workflow

1. Register an account
2. Create a simulation (choose nodes, devices, tasks, algorithm)
3. Go to simulation → click **Generate Tasks**
4. Click **Run Simulation** → watch live training progress
5. View **Analytics** → explore charts
6. Download **PDF Report** for FYP defense

---

## Architecture

Browser (Blade + Alpine.js + Chart.js)
↕ HTTP
Laravel 13 (Controllers · Models · Routes)
↕ MySQL (simulations · tasks · results · training_runs)
↕ HTTP (PythonAiService)
FastAPI (Python AI Engine)
├── EdgeComputingEnv (Gymnasium)
├── PPO Trainer (Stable-Baselines3)
└── DQN Trainer (Stable-Baselines3)