@props(['task'])

<tr class="border-b border-slate-800 hover:bg-slate-800/30 transition-colors">
    <td class="py-3 px-4">
        <span class="font-mono text-xs text-slate-300">
            {{ $task->task_id_label ?? 'TASK-' . str_pad($task->id, 4, '0', STR_PAD_LEFT) }}
        </span>
    </td>
    <td class="py-3 px-4">
        <span class="text-xs text-slate-400">
            {{ $task->iotDevice?->name ?? '—' }}
        </span>
    </td>
    <td class="py-3 px-4">
        <x-ui.badge :color="$task->priority_color">{{ ucfirst($task->priority) }}</x-ui.badge>
    </td>
    <td class="py-3 px-4 text-sm text-slate-300">
        {{ round($task->cpu_requirement, 1) }}%
    </td>
    <td class="py-3 px-4 text-sm text-slate-300">
        {{ round($task->memory_requirement) }} MB
    </td>
    <td class="py-3 px-4 text-sm text-slate-300">
        {{ round($task->task_size, 2) }} MB
    </td>
    <td class="py-3 px-4 text-sm text-slate-300">
        {{ round($task->deadline, 1) }}s
    </td>
    <td class="py-3 px-4">
        @if($task->edgeNode)
            <span class="text-xs text-slate-400">{{ $task->edgeNode->name }}</span>
        @else
            <span class="text-xs text-slate-600">—</span>
        @endif
    </td>
    <td class="py-3 px-4">
        <x-ui.badge :color="$task->status_color">{{ ucfirst($task->status) }}</x-ui.badge>
    </td>
    <td class="py-3 px-4 text-sm text-slate-300">
        {{ $task->latency ? round($task->latency, 1) . ' ms' : '—' }}
    </td>
</tr>
