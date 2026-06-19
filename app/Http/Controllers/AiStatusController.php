<?php

namespace App\Http\Controllers;

use App\Services\PythonAiService;

class AiStatusController extends Controller
{
    public function __construct(private PythonAiService $ai) {}

    public function health()
    {
        return response()->json($this->ai->health());
    }

    public function envInfo()
    {
        return response()->json($this->ai->envInfo());
    }
}