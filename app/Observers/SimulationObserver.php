<?php

namespace App\Observers;

use App\Models\EdgeNode;
use App\Models\IotDevice;
use App\Models\Simulation;

class SimulationObserver
{
    public function created(Simulation $simulation): void
    {
        $this->provisionEdgeNodes($simulation);
        $this->provisionIotDevices($simulation);
    }

    // ── Edge Nodes ─────────────────────────────────────────────

    private function provisionEdgeNodes(Simulation $simulation): void
    {
        $nodeConfigs = [
            ['cpu' => 100.0, 'memory' => 8192],
            ['cpu' => 100.0, 'memory' => 4096],
            ['cpu' => 100.0, 'memory' => 16384],
            ['cpu' => 100.0, 'memory' => 8192],
            ['cpu' => 100.0, 'memory' => 4096],
            ['cpu' => 100.0, 'memory' => 8192],
            ['cpu' => 100.0, 'memory' => 16384],
            ['cpu' => 100.0, 'memory' => 4096],
            ['cpu' => 100.0, 'memory' => 8192],
            ['cpu' => 100.0, 'memory' => 16384],
        ];

        for ($i = 0; $i < $simulation->num_edge_nodes; $i++) {
            $config = $nodeConfigs[$i % count($nodeConfigs)];
            EdgeNode::create([
                'simulation_id'          => $simulation->id,
                'name'                   => 'Edge-Node-' . str_pad($i + 1, 2, '0', STR_PAD_LEFT),
                'cpu_capacity'           => $config['cpu'],
                'memory_capacity'        => $config['memory'],
                'cpu_used'               => 0.0,
                'memory_used'            => 0.0,
                'queue_length'           => 0,
                'utilization_percentage' => 0.0,
                'status'                 => 'idle',
            ]);
        }
    }

    // ── IoT Devices ────────────────────────────────────────────

    private function provisionIotDevices(Simulation $simulation): void
    {
        $types = [
            'temperature_sensor',
            'motion_sensor',
            'camera',
            'actuator',
            'gateway',
        ];

        $prefixes = [
            'temperature_sensor' => 'TEMP',
            'motion_sensor'      => 'MOT',
            'camera'             => 'CAM',
            'actuator'           => 'ACT',
            'gateway'            => 'GW',
        ];

        for ($i = 0; $i < $simulation->num_iot_devices; $i++) {
            $type = $types[$i % count($types)];
            IotDevice::create([
                'simulation_id' => $simulation->id,
                'name'          => $prefixes[$type] . '-' . str_pad($i + 1, 3, '0', STR_PAD_LEFT),
                'type'          => $type,
                'battery_level' => rand(60, 100),
                'is_active'     => true,
            ]);
        }
    }
}