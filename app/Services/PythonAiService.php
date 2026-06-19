<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PythonAiService
{
    private string $baseUrl;
    private int    $timeout;

    public function __construct()
    {
        $this->baseUrl = config('services.python_ai.url', 'http://127.0.0.1:8001');
        $this->timeout = config('services.python_ai.timeout', 300);
    }

    // ── Health ─────────────────────────────────────────────────

    public function health(): array
    {
        try {
            $response = Http::timeout(5)->get("{$this->baseUrl}/health");
            return $response->json() ?? ['status' => 'unreachable'];
        } catch (ConnectionException) {
            return ['status' => 'unreachable', 'error' => 'FastAPI server is not running.'];
        }
    }

    public function isOnline(): bool
    {
        return ($this->health()['status'] ?? '') === 'ok';
    }

    // ── Training ───────────────────────────────────────────────

    public function startTraining(array $payload): array
    {
        try {
            $response = Http::timeout($this->timeout)
                ->post("{$this->baseUrl}/train", $payload);

            if ($response->failed()) {
                Log::error('FastAPI training start failed', ['body' => $response->body()]);
                return ['error' => 'Training request failed: ' . $response->body()];
            }

            return $response->json();
        } catch (ConnectionException $e) {
            return ['error' => 'Cannot connect to AI engine. Is FastAPI running?'];
        }
    }

    public function trainingStatus(int $trainingRunId): array
    {
        try {
            $response = Http::timeout(10)
                ->get("{$this->baseUrl}/train/{$trainingRunId}/status");

            if ($response->status() === 404) {
                return ['status' => 'not_found'];
            }

            return $response->json() ?? [];
        } catch (ConnectionException) {
            return ['status' => 'unreachable'];
        }
    }

    // ── Inference ──────────────────────────────────────────────

    public function runInference(array $payload): array
    {
        try {
            $response = Http::timeout(120)
                ->post("{$this->baseUrl}/infer", $payload);

            if ($response->failed()) {
                return ['error' => 'Inference failed: ' . $response->body()];
            }

            return $response->json();
        } catch (ConnectionException) {
            return ['error' => 'Cannot connect to AI engine.'];
        }
    }

    // ── Environment Info ───────────────────────────────────────

    public function envInfo(): array
    {
        try {
            return Http::timeout(10)
                ->get("{$this->baseUrl}/env/info")
                ->json() ?? [];
        } catch (ConnectionException) {
            return [];
        }
    }
}
