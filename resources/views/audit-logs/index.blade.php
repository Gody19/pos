@extends('layouts.app')

@section('title', 'Audit Logs')

@section('content')
    <x-page-header title="Audit Logs" description="A chronological record of sensitive actions across the system." />

    <x-card>
        <x-slot:actions>
            <form method="GET" class="flex flex-col gap-2 sm:flex-row sm:items-center">
                <select name="event" class="input sm:w-48" onchange="this.form.submit()">
                    <option value="">All events</option>
                    @foreach ($events as $event)
                        <option value="{{ $event }}" @selected(request('event') === $event)>{{ $event }}</option>
                    @endforeach
                </select>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search details…"
                    class="input sm:w-56" autocomplete="off">
                <button type="submit" class="btn-secondary">Filter</button>
            </form>
        </x-slot:actions>

        <div class="overflow-x-auto">
            <table class="table">
                <thead>
                    <tr>
                        <th>Time</th>
                        <th>User</th>
                        <th>Event</th>
                        <th>Target</th>
                        <th>Details</th>
                        <th>IP</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($logs as $log)
                        <tr>
                            <td class="text-xs text-gray-500">{{ $log->created_at->format('d M Y H:i:s') }}</td>
                            <td>{{ $log->user?->name ?? 'System' }}</td>
                            <td>
                                <x-badge :color="match ($log->event) {
                                    'login', 'logout' => 'blue',
                                    'sale_created', 'shift_opened' => 'green',
                                    'sale_cancelled', 'inventory_adjusted' => 'yellow',
                                    'user_created', 'user_updated', 'user_deleted' => 'purple',
                                    default => 'gray',
                                }">{{ $log->event }}</x-badge>
                            </td>
                            <td class="text-xs text-gray-500">
                                @if ($log->auditable_type)
                                    {{ class_basename($log->auditable_type) }} #{{ $log->auditable_id }}
                                @else
                                    —
                                @endif
                            </td>
                            <td class="max-w-md">
                                <pre class="truncate text-xs text-gray-500">{{ json_encode($log->details) }}</pre>
                            </td>
                            <td class="font-mono text-xs text-gray-400">{{ $log->ip_address }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
                                <x-empty-state title="No audit entries" description="Audit events will be recorded here automatically." />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-gray-100 p-4">
            {{ $logs->links() }}
        </div>
    </x-card>
@endsection