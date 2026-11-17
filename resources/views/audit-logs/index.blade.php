@extends('layouts.app')

@section('title', 'System Audit Logs')
@section('header-title', 'System Audit Trail & Compliance')
@section('header-subtitle', 'Immutable chronological audit record of all platform operations and master data modifications')

@section('content')
<div class="space-y-6">

    <!-- Top Action & Filter Bar -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h2 class="text-lg font-bold text-slate-900 tracking-tight">Platform Audit History</h2>
            <p class="text-xs text-slate-500">Total <strong class="text-slate-800">{{ number_format($totalLogs) }}</strong> audit log entries captured</p>
        </div>

        <!-- Filter Form -->
        <form method="GET" action="{{ route('audit-logs.index') }}" class="flex flex-wrap items-center gap-2 self-start md:self-auto">
            <select name="action" class="px-3 py-1.5 bg-white border border-slate-300 rounded-lg text-xs font-semibold text-slate-700 focus:outline-none focus:ring-2 focus:ring-ocean-500">
                <option value="">All Actions</option>
                <option value="CREATED" {{ request('action') === 'CREATED' ? 'selected' : '' }}>CREATED</option>
                <option value="UPDATED" {{ request('action') === 'UPDATED' ? 'selected' : '' }}>UPDATED</option>
                <option value="DELETED" {{ request('action') === 'DELETED' ? 'selected' : '' }}>DELETED</option>
                <option value="ETA_UPDATED" {{ request('action') === 'ETA_UPDATED' ? 'selected' : '' }}>ETA_UPDATED</option>
                <option value="STATUS_CHANGED" {{ request('action') === 'STATUS_CHANGED' ? 'selected' : '' }}>STATUS_CHANGED</option>
            </select>

            <select name="entity_type" class="px-3 py-1.5 bg-white border border-slate-300 rounded-lg text-xs font-semibold text-slate-700 focus:outline-none focus:ring-2 focus:ring-ocean-500">
                <option value="">All Entities</option>
                <option value="Shipment" {{ request('entity_type') === 'Shipment' ? 'selected' : '' }}>Shipment</option>
                <option value="Carrier" {{ request('entity_type') === 'Carrier' ? 'selected' : '' }}>Carrier</option>
                <option value="Location" {{ request('entity_type') === 'Location' ? 'selected' : '' }}>Location</option>
                <option value="TrackingEvent" {{ request('entity_type') === 'TrackingEvent' ? 'selected' : '' }}>TrackingEvent</option>
                <option value="ShipmentException" {{ request('entity_type') === 'ShipmentException' ? 'selected' : '' }}>ShipmentException</option>
            </select>

            <button type="submit" class="px-3.5 py-1.5 bg-ocean-600 hover:bg-ocean-500 text-white rounded-lg text-xs font-semibold shadow-sm transition-colors">
                Filter
            </button>

            @if(request()->hasAny(['action', 'entity_type']))
                <a href="{{ route('audit-logs.index') }}" class="px-2.5 py-1.5 text-xs text-slate-500 hover:text-slate-800 transition-colors">
                    Reset
                </a>
            @endif
        </form>
    </div>

    <!-- Audit Logs Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-500 font-semibold uppercase tracking-wider border-b border-slate-200/80">
                    <tr>
                        <th class="px-5 py-3.5">Timestamp (UTC)</th>
                        <th class="px-5 py-3.5">Actor User</th>
                        <th class="px-5 py-3.5">Action</th>
                        <th class="px-5 py-3.5">Target Entity</th>
                        <th class="px-5 py-3.5">IP Address</th>
                        <th class="px-5 py-3.5">Modified Data (Old &rarr; New)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse($auditLogs as $log)
                        <tr class="hover:bg-slate-50/70 transition-colors">
                            <td class="px-5 py-3.5 font-mono text-slate-500 whitespace-nowrap">
                                <div class="font-bold text-slate-800">{{ $log->created_at->format('d M Y') }}</div>
                                <div class="text-[11px] text-slate-400">{{ $log->created_at->format('H:i:s') }} UTC</div>
                            </td>

                            <td class="px-5 py-3.5 whitespace-nowrap">
                                @if($log->user)
                                    <div class="font-bold text-slate-900">{{ $log->user->name }}</div>
                                    <div class="text-[11px] text-slate-400 flex items-center space-x-1.5">
                                        <span class="px-1.5 py-0.2 rounded text-[10px] uppercase font-semibold {{ $log->user->isAdmin() ? 'bg-purple-100 text-purple-700' : ($log->user->isOperator() ? 'bg-sky-100 text-sky-700' : 'bg-slate-100 text-slate-700') }}">
                                            {{ $log->user->role }}
                                        </span>
                                        <span>{{ $log->user->email }}</span>
                                    </div>
                                @else
                                    <span class="text-slate-400 italic">System / Seeder</span>
                                @endif
                            </td>

                            <td class="px-5 py-3.5 whitespace-nowrap">
                                @php
                                    $actionClass = match($log->action) {
                                        'CREATED' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
                                        'UPDATED', 'ETA_UPDATED' => 'bg-sky-100 text-sky-800 border-sky-200',
                                        'STATUS_CHANGED' => 'bg-amber-100 text-amber-800 border-amber-200',
                                        'DELETED' => 'bg-rose-100 text-rose-800 border-rose-200',
                                        default => 'bg-slate-100 text-slate-800 border-slate-200',
                                    };
                                @endphp
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold border {{ $actionClass }}">
                                    {{ $log->action }}
                                </span>
                            </td>

                            <td class="px-5 py-3.5 whitespace-nowrap">
                                <span class="font-bold text-slate-900">{{ $log->entity_type }}</span>
                                <span class="text-slate-400 font-mono text-[11px]">#{{ $log->entity_id }}</span>
                            </td>

                            <td class="px-5 py-3.5 font-mono text-[11px] text-slate-500 whitespace-nowrap">
                                {{ $log->ip_address ?? '127.0.0.1' }}
                            </td>

                            <td class="px-5 py-3.5 min-w-[280px]">
                                @if(!empty($log->old_values) || !empty($log->new_values))
                                    <details class="cursor-pointer group">
                                        <summary class="text-[11px] font-semibold text-ocean-600 hover:text-ocean-700 select-none">
                                            <span>View Diff Details</span>
                                        </summary>
                                        <div class="mt-2 p-2.5 rounded-lg bg-slate-50 border border-slate-200 text-[10px] font-mono space-y-1.5 max-h-48 overflow-y-auto">
                                            @if(!empty($log->old_values))
                                                <div>
                                                    <span class="text-rose-600 font-bold uppercase">Before:</span>
                                                    <pre class="whitespace-pre-wrap text-slate-600">{{ json_encode($log->old_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                                                </div>
                                            @endif
                                            @if(!empty($log->new_values))
                                                <div class="{{ !empty($log->old_values) ? 'pt-1.5 border-t border-slate-200' : '' }}">
                                                    <span class="text-emerald-600 font-bold uppercase">After:</span>
                                                    <pre class="whitespace-pre-wrap text-slate-700">{{ json_encode($log->new_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                                                </div>
                                            @endif
                                        </div>
                                    </details>
                                @else
                                    <span class="text-slate-400 text-[11px] italic">No payload captured</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-10 text-center text-slate-400">
                                No audit log records found for the selected filter.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($auditLogs->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $auditLogs->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
