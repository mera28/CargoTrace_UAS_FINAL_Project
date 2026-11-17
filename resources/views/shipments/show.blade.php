@extends('layouts.app')

@php
    $latestEvent = $shipment->trackingEvents->sortByDesc('event_time')->first();
    $trackingService = app(\App\Services\TrackingService::class);
    $allowedNextStatuses = $trackingService->getAllowedNextStatuses($shipment->current_status);
    $thresholdHours = config('cargotrace.delay_threshold_hours', 12);
    $exceptionTypes = config('cargotrace.exception_types', ['CUSTOMS_HOLD', 'WEATHER_DELAY', 'PORT_CONGESTION', 'DAMAGED', 'MISROUTED', 'DOCUMENTATION_ISSUE']);
@endphp

@section('title', 'Shipment ' . $shipment->shipment_number)
@section('header-title', 'Consignment Details')
@section('header-subtitle', 'Real-time tracking, ETA evaluation, and risk incidents for ' . $shipment->shipment_number)

@section('content')
<div class="space-y-6">

    <!-- Top Action & Navigation Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <a href="{{ route('shipments.index') }}" class="inline-flex items-center space-x-2 text-xs font-semibold text-slate-500 hover:text-slate-800 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            <span>Back to Shipments Directory</span>
        </a>

        @if(auth()->user()->isAdmin() || auth()->user()->isOperator())
            <div class="flex items-center space-x-2">
                @if($shipment->current_status !== 'DELIVERED')
                    <a href="{{ route('shipments.edit', $shipment) }}" class="inline-flex items-center space-x-1.5 px-3.5 py-2 border border-slate-300 bg-white hover:bg-slate-50 text-slate-700 rounded-xl text-xs font-semibold shadow-sm transition-colors">
                        <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        <span>Edit Consignment</span>
                    </a>
                @endif

                @if($shipment->current_status === 'CREATED')
                    <form action="{{ route('shipments.destroy', $shipment) }}" method="POST" onsubmit="return confirm('Are you sure you want to permanently delete this consignment?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="inline-flex items-center space-x-1.5 px-3.5 py-2 border border-rose-200 bg-white hover:bg-rose-50 text-rose-600 rounded-xl text-xs font-semibold shadow-sm transition-colors">
                            <svg class="w-3.5 h-3.5 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            <span>Delete</span>
                        </button>
                    </form>
                @endif
            </div>
        @endif
    </div>

    <!-- Header Hero Card -->
    <div class="bg-gradient-to-r from-navy-900 via-navy-800 to-slate-900 rounded-2xl p-6 text-white shadow-lg border border-slate-800 relative overflow-hidden">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div>
                <div class="flex items-center space-x-2.5 mb-2">
                    <span class="text-xs font-mono font-bold px-2 py-0.5 rounded bg-ocean-500/20 text-ocean-400 border border-ocean-500/30">
                        {{ $shipment->shipment_number }}
                    </span>
                    <span class="text-xs text-slate-400">&bull;</span>
                    <span class="text-xs text-slate-300 font-semibold">{{ $shipment->carrier->name }} ({{ $shipment->carrier->type }})</span>
                </div>

                <h1 class="text-2xl font-extrabold tracking-tight text-white flex items-center space-x-3">
                    <span>{{ $shipment->originLocation->name }}</span>
                    <span class="text-ocean-400">&rarr;</span>
                    <span>{{ $shipment->destinationLocation->name }}</span>
                </h1>

                <p class="text-xs text-slate-300 mt-2 max-w-2xl">
                    <strong class="text-slate-100">Commodity:</strong> {{ $shipment->cargo_type }} &bull;
                    <strong class="text-slate-100">Shipper:</strong> {{ $shipment->customer->name }} ({{ $shipment->customer->company_name ?? 'Individual' }})
                </p>
            </div>

            <!-- Badges Section -->
            <div class="flex flex-col sm:flex-row md:flex-col items-start md:items-end gap-2.5">
                <!-- Status Badge -->
                @if($shipment->current_status === 'DELIVERED')
                    <span class="px-3 py-1 rounded-full text-xs font-bold bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">
                        STATUS: DELIVERED
                    </span>
                @elseif($shipment->current_status === 'IN_TRANSIT')
                    <span class="px-3 py-1 rounded-full text-xs font-bold bg-sky-500/20 text-sky-300 border border-sky-500/30 flex items-center space-x-1.5">
                        <span class="w-2 h-2 rounded-full bg-sky-400 animate-pulse"></span>
                        <span>STATUS: IN TRANSIT</span>
                    </span>
                @elseif($shipment->current_status === 'PICKED_UP')
                    <span class="px-3 py-1 rounded-full text-xs font-bold bg-indigo-500/20 text-indigo-300 border border-indigo-500/30">
                        STATUS: PICKED UP
                    </span>
                @elseif($shipment->current_status === 'BOOKED')
                    <span class="px-3 py-1 rounded-full text-xs font-bold bg-purple-500/20 text-purple-300 border border-purple-500/30">
                        STATUS: BOOKED
                    </span>
                @else
                    <span class="px-3 py-1 rounded-full text-xs font-bold bg-slate-500/20 text-slate-300 border border-slate-500/30">
                        STATUS: CREATED
                    </span>
                @endif

                <!-- Delay Indicator -->
                @if($shipment->is_delayed)
                    <div class="px-3 py-1 rounded-lg text-xs font-bold {{ $shipment->delay_severity === 'HIGH' ? 'bg-rose-500/20 text-rose-300 border border-rose-500/40' : 'bg-amber-500/20 text-amber-300 border border-amber-500/40' }}">
                        Delayed +{{ $shipment->delay_hours }}h ({{ $shipment->delay_severity }} Severity)
                    </div>
                @else
                    <div class="px-3 py-1 rounded-lg text-xs font-semibold bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 flex items-center space-x-1.5">
                        <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                        <span>Operating on Schedule</span>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Current Location Banner -->
    <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex items-start space-x-3.5">
            <div class="w-11 h-11 rounded-xl bg-gradient-to-tr from-ocean-600 to-sky-400 text-white flex items-center justify-center flex-shrink-0 shadow-md shadow-ocean-600/20">
                <svg class="w-6 h-6 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
            </div>
            <div>
                <div class="text-[11px] font-bold text-ocean-600 uppercase tracking-wider flex items-center space-x-1.5">
                    <span class="w-2 h-2 rounded-full bg-ocean-500 animate-ping"></span>
                    <span>Current Position & Milestone</span>
                </div>
                <div class="text-base font-extrabold text-slate-900 mt-0.5">
                    {{ $latestEvent ? $latestEvent->location_name : $shipment->originLocation->name }}
                </div>
                <p class="text-xs text-slate-500 mt-0.5">
                    {{ $latestEvent ? $latestEvent->description : 'Consignment registered at origin terminal' }}
                </p>
            </div>
        </div>

        <div class="flex items-center space-x-6 text-xs text-slate-500 font-mono border-t md:border-t-0 md:border-l border-slate-100 pt-3 md:pt-0 md:pl-6">
            @if($latestEvent && $latestEvent->latitude && $latestEvent->longitude)
                <div>
                    <div class="text-[10px] text-slate-400 uppercase font-sans font-semibold">GPS Coordinates</div>
                    <div class="text-slate-800 font-bold mt-0.5">{{ number_format($latestEvent->latitude, 4) }}&deg;, {{ number_format($latestEvent->longitude, 4) }}&deg;</div>
                </div>
            @endif
            <div>
                <div class="text-[10px] text-slate-400 uppercase font-sans font-semibold">Last Recorded</div>
                <div class="text-slate-800 font-bold mt-0.5">
                    {{ $latestEvent ? $latestEvent->event_time->format('d M Y - H:i') : $shipment->created_at->format('d M Y - H:i') }} UTC
                </div>
            </div>
        </div>
    </div>

    <!-- ETA & DELAY DETECTION CONTROL CARD (Phase 10 Implementation) -->
    <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm space-y-5">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-100 pb-3">
            <div>
                <h3 class="text-sm font-bold text-slate-900 flex items-center space-x-2">
                    <svg class="w-4 h-4 text-ocean-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>ETA Evaluation & Delay Intelligence</span>
                </h3>
                <p class="text-xs text-slate-500">
                    Business Rule: Threshold is <strong class="text-slate-700">{{ $thresholdHours }} Hours</strong>. Delays &ge; {{ $thresholdHours }}h trigger proactive alerts & severity rating.
                </p>
            </div>

            @if(auth()->user()->isAdmin() || auth()->user()->isOperator())
                <button type="button" onclick="openEtaModal()"
                    class="inline-flex items-center space-x-1.5 px-3.5 py-2 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-xs font-semibold shadow-sm transition-colors self-start sm:self-auto">
                    <svg class="w-3.5 h-3.5 text-ocean-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                    <span>Recalibrate ETA</span>
                </button>
            @endif
        </div>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 text-xs">
            <!-- Expected Arrival -->
            <div class="p-4 rounded-xl bg-slate-50 border border-slate-200/80">
                <div class="text-slate-400 font-semibold uppercase tracking-wider text-[10px]">Expected Arrival (Contracted)</div>
                <div class="text-sm font-bold text-slate-900 mt-1">{{ $shipment->expected_arrival->format('d M Y - H:i') }} UTC</div>
                <div class="text-[11px] text-slate-500 mt-0.5">Destination: {{ $shipment->destinationLocation->name }}</div>
            </div>

            <!-- Estimated Arrival -->
            <div class="p-4 rounded-xl {{ $shipment->is_delayed ? 'bg-amber-50/70 border border-amber-200' : 'bg-slate-50 border border-slate-200/80' }}">
                <div class="text-slate-400 font-semibold uppercase tracking-wider text-[10px]">Estimated Arrival (Current Forecast)</div>
                <div class="text-sm font-bold {{ $shipment->is_delayed ? 'text-amber-900' : 'text-slate-900' }} mt-1">
                    {{ $shipment->estimated_arrival ? $shipment->estimated_arrival->format('d M Y - H:i') . ' UTC' : 'Pending Calculation' }}
                </div>
                <div class="text-[11px] {{ $shipment->is_delayed ? 'text-amber-700 font-semibold' : 'text-slate-500' }} mt-0.5">
                    {{ $shipment->is_delayed ? "+{$shipment->delay_hours}h past contract" : 'Aligned with baseline' }}
                </div>
            </div>

            <!-- Delay Duration & Severity -->
            <div class="p-4 rounded-xl {{ $shipment->is_delayed ? ($shipment->delay_severity === 'HIGH' ? 'bg-rose-50 border border-rose-200' : 'bg-amber-50 border border-amber-200') : 'bg-emerald-50/50 border border-emerald-200/70' }}">
                <div class="text-slate-400 font-semibold uppercase tracking-wider text-[10px]">Calculated Variance & Severity</div>
                <div class="text-sm font-bold mt-1 flex items-center space-x-2">
                    <span class="{{ $shipment->is_delayed ? ($shipment->delay_severity === 'HIGH' ? 'text-rose-700' : 'text-amber-800') : 'text-emerald-700' }}">
                        {{ $shipment->delay_hours }} Hours
                    </span>
                    @if($shipment->is_delayed)
                        <span class="px-2 py-0.5 rounded text-[10px] font-extrabold uppercase {{ $shipment->delay_severity === 'HIGH' ? 'bg-rose-200 text-rose-900' : ($shipment->delay_severity === 'MEDIUM' ? 'bg-amber-200 text-amber-900' : 'bg-sky-200 text-sky-900') }}">
                            {{ $shipment->delay_severity }}
                        </span>
                    @else
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800 uppercase">
                            ON-TIME
                        </span>
                    @endif
                </div>
                <div class="text-[11px] text-slate-500 mt-0.5">Threshold trigger: &ge; {{ $thresholdHours }}h</div>
            </div>

            <!-- KPI Compliance -->
            <div class="p-4 rounded-xl bg-slate-50 border border-slate-200/80">
                <div class="text-slate-400 font-semibold uppercase tracking-wider text-[10px]">Primary KPI Compliance</div>
                <div class="text-sm font-bold mt-1 flex items-center space-x-1.5">
                    @if(!$shipment->is_delayed)
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                        <span class="text-emerald-700 font-bold">Compliant (On-Time)</span>
                    @else
                        <span class="w-2.5 h-2.5 rounded-full bg-rose-500 animate-pulse"></span>
                        <span class="text-rose-700 font-bold">KPI Breached</span>
                    @endif
                </div>
                <div class="text-[11px] text-slate-500 mt-0.5">{{ config('cargotrace.primary_kpi') }}</div>
            </div>
        </div>

        @if($shipment->delay_reason)
            <div class="p-3.5 rounded-xl bg-amber-50 border border-amber-200 text-xs text-amber-900 flex items-start space-x-2">
                <svg class="w-4 h-4 text-amber-600 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                <div>
                    <strong class="font-bold">Official Delay Justification:</strong> {{ $shipment->delay_reason }}
                </div>
            </div>
        @endif
    </div>

    <!-- TRACKING TIMELINE & ADD EVENT SECTION (Phase 9) -->
    <div class="grid grid-cols-1 {{ (auth()->user()->isAdmin() || auth()->user()->isOperator()) && $shipment->current_status !== 'DELIVERED' ? 'lg:grid-cols-3' : '' }} gap-6">

        <!-- Tracking Timeline -->
        <div class="{{ (auth()->user()->isAdmin() || auth()->user()->isOperator()) && $shipment->current_status !== 'DELIVERED' ? 'lg:col-span-2' : 'w-full' }} bg-white rounded-2xl border border-slate-200 p-6 shadow-sm space-y-5">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div>
                    <h3 class="text-sm font-bold text-slate-900 flex items-center space-x-2">
                        <svg class="w-4 h-4 text-ocean-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>Historical Tracking Timeline</span>
                    </h3>
                    <p class="text-xs text-slate-500">Immutable audit chain of voyage events ordered chronologically</p>
                </div>
                <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-600 font-mono">
                    {{ $shipment->trackingEvents->count() }} Milestones
                </span>
            </div>

            @if($shipment->trackingEvents->count() > 0)
                <div class="relative pl-8 space-y-6 before:absolute before:left-3 before:top-2 before:bottom-2 before:w-0.5 before:bg-slate-200">
                    @foreach($shipment->trackingEvents->sortBy('event_time') as $idx => $event)
                        @php
                            $isDelivered = $event->status === 'DELIVERED';
                            $isExceptionEvent = str_starts_with($event->description, '[EXCEPTION');
                            $isWarning = $isExceptionEvent || str_contains(strtolower($event->description), 'customs') || str_contains(strtolower($event->description), 'hold') || str_contains(strtolower($event->description), 'delay');
                        @endphp
                        <div class="relative group">
                            <!-- Timeline Dot Icon -->
                            <div class="absolute -left-8 top-0.5 w-6 h-6 rounded-full flex items-center justify-center ring-4 ring-white shadow-sm {{ $isDelivered ? 'bg-emerald-600 text-white' : ($isWarning ? 'bg-amber-500 text-white' : 'bg-ocean-600 text-white') }}">
                                @if($isDelivered)
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                @elseif($isWarning)
                                    <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                                @else
                                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/></svg>
                                @endif
                            </div>

                            <!-- Milestone Box -->
                            <div class="p-4 rounded-xl border {{ $isExceptionEvent ? 'bg-rose-50/50 border-rose-200' : ($isWarning ? 'bg-amber-50/40 border-amber-200' : 'bg-slate-50/70 border-slate-200') }} shadow-2xs transition-colors">
                                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-1 mb-1">
                                    <div class="flex items-center space-x-2">
                                        <span class="px-2 py-0.5 rounded text-[11px] font-bold uppercase {{ $isDelivered ? 'bg-emerald-100 text-emerald-800' : ($isExceptionEvent ? 'bg-rose-100 text-rose-800' : ($isWarning ? 'bg-amber-100 text-amber-800' : 'bg-sky-100 text-sky-800')) }}">
                                            {{ $event->status }}
                                        </span>
                                        <span class="text-sm font-bold text-slate-900">{{ $event->location_name }}</span>
                                    </div>
                                    <span class="text-xs text-slate-400 font-mono">{{ $event->event_time->format('d M Y - H:i') }} UTC</span>
                                </div>

                                <p class="text-xs text-slate-700 mt-1.5 leading-relaxed">{{ $event->description }}</p>

                                <div class="mt-2.5 pt-2 border-t border-slate-200/60 flex items-center justify-between text-[11px] text-slate-400 font-mono">
                                    <span>
                                        @if($event->latitude && $event->longitude)
                                            GPS: {{ number_format($event->latitude, 4) }}&deg;, {{ number_format($event->longitude, 4) }}&deg;
                                        @else
                                            Position: Verified
                                        @endif
                                    </span>
                                    <span>Recorded by: <strong class="text-slate-600 font-sans">{{ $event->recorder->name ?? 'System' }}</strong></span>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="p-8 text-center text-xs text-slate-400 bg-slate-50 rounded-xl border border-dashed border-slate-200">
                    No tracking events recorded yet.
                </div>
            @endif
        </div>

        <!-- Add Tracking Event Form (Admin & Operator only) -->
        @if((auth()->user()->isAdmin() || auth()->user()->isOperator()) && $shipment->current_status !== 'DELIVERED')
            <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm space-y-4 self-start">
                <div class="border-b border-slate-100 pb-3">
                    <h3 class="text-sm font-bold text-slate-900 flex items-center space-x-2">
                        <svg class="w-4 h-4 text-ocean-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        <span>Add Tracking Milestone</span>
                    </h3>
                    <p class="text-xs text-slate-500">Progressive status milestone for this consignment</p>
                </div>

                @if(count($allowedNextStatuses) > 0)
                    <form action="{{ route('shipments.tracking-events.store', $shipment) }}" method="POST" class="space-y-4">
                        @csrf

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
                                Next Allowed Status *
                            </label>
                            <select name="status" required class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-lg text-xs font-bold text-slate-900 focus:bg-white focus:outline-none focus:ring-2 focus:ring-ocean-500">
                                @foreach($allowedNextStatuses as $nextSt)
                                    <option value="{{ $nextSt }}">
                                        {{ $nextSt }}
                                        @if($nextSt === 'IN_TRANSIT') (Intermediate Waypoint)
                                        @elseif($nextSt === 'DELIVERED') (Final Destination)
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Location Name *</label>
                            <input type="text" name="location_name" required placeholder="e.g. Red Sea Transit"
                                class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-lg text-xs text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-ocean-500">
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Latitude</label>
                                <input type="number" step="0.0000001" name="latitude" placeholder="e.g. 21.4858"
                                    class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-lg text-xs font-mono text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-ocean-500">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Longitude</label>
                                <input type="number" step="0.0000001" name="longitude" placeholder="e.g. 39.1925"
                                    class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-lg text-xs font-mono text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-ocean-500">
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Event Timestamp (UTC) *</label>
                            <input type="datetime-local" name="event_time" value="{{ now()->format('Y-m-d\TH:i') }}" required
                                class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-lg text-xs text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-ocean-500">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Milestone Description *</label>
                            <textarea name="description" rows="3" required placeholder="Describe voyage milestone..."
                                class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-lg text-xs text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-ocean-500"></textarea>
                        </div>

                        <button type="submit" class="w-full py-2.5 px-4 bg-gradient-to-r from-ocean-600 to-sky-600 hover:from-ocean-500 hover:to-sky-500 text-white font-bold rounded-xl text-xs shadow-md shadow-ocean-600/30 transition-all flex items-center justify-center space-x-1.5">
                            <span>Record Milestone</span>
                        </button>
                    </form>
                @endif
            </div>
        @endif

    </div>

    <!-- SHIPMENT EXCEPTIONS & RISK INCIDENTS (Phase 10 Implementation) -->
    <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm space-y-5">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-100 pb-3">
            <div>
                <h3 class="text-sm font-bold text-slate-900 flex items-center space-x-2">
                    <svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    <span>Shipment Exceptions & Customs Clearance Holds</span>
                </h3>
                <p class="text-xs text-slate-500">Focus Scenario: Primary disruption type is <strong class="text-slate-700">CUSTOMS_HOLD</strong> along the SEA &rarr; Europe corridor</p>
            </div>

            @if(auth()->user()->isAdmin() || auth()->user()->isOperator())
                <button type="button" onclick="openExceptionModal()"
                    class="inline-flex items-center space-x-1.5 px-3.5 py-2 bg-amber-600 hover:bg-amber-500 text-white rounded-xl text-xs font-semibold shadow-sm transition-colors self-start sm:self-auto">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    <span>+ Report Incident / Exception</span>
                </button>
            @endif
        </div>

        @if($shipment->exceptions->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @foreach($shipment->exceptions as $exc)
                    <div class="p-4 rounded-xl border {{ $exc->status === 'OPEN' ? 'bg-rose-50/50 border-rose-200' : ($exc->status === 'INVESTIGATING' ? 'bg-amber-50/50 border-amber-200' : 'bg-slate-50 border-slate-200') }} text-xs space-y-3">
                        <div class="flex items-start justify-between gap-2">
                            <div>
                                <div class="flex items-center space-x-2">
                                    <span class="font-extrabold text-slate-900 text-sm">{{ $exc->exception_type }}</span>
                                    <span class="px-2 py-0.5 rounded text-[10px] font-extrabold uppercase {{ $exc->severity === 'HIGH' ? 'bg-rose-100 text-rose-800' : ($exc->severity === 'MEDIUM' ? 'bg-amber-100 text-amber-800' : 'bg-sky-100 text-sky-800') }}">
                                        {{ $exc->severity }} SEVERITY
                                    </span>
                                </div>
                                <div class="text-[10px] text-slate-400 font-mono mt-0.5">Reported by {{ $exc->reporter->name ?? 'Staff' }}</div>
                            </div>

                            <span class="px-2.5 py-1 rounded-full text-[10px] font-extrabold uppercase tracking-wide {{ $exc->status === 'RESOLVED' ? 'bg-emerald-100 text-emerald-800' : ($exc->status === 'INVESTIGATING' ? 'bg-amber-100 text-amber-800' : 'bg-rose-100 text-rose-800') }}">
                                {{ $exc->status }}
                            </span>
                        </div>

                        <p class="text-xs text-slate-700 leading-relaxed bg-white/70 p-2.5 rounded-lg border border-slate-200/50">
                            {{ $exc->description }}
                        </p>

                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pt-1 border-t border-slate-200/60 text-[11px] text-slate-400 font-mono">
                            <div>
                                <span>Detected: {{ $exc->detected_at->format('d M Y H:i') }}</span>
                                @if($exc->resolved_at)
                                    <span class="text-emerald-700 font-semibold ml-2">Resolved: {{ $exc->resolved_at->format('d M Y H:i') }}</span>
                                @endif
                            </div>

                            <!-- Fast Action Status Transition (Admin & Operator only) -->
                            @if(auth()->user()->isAdmin() || auth()->user()->isOperator())
                                <div class="flex items-center space-x-1.5 self-end sm:self-auto font-sans">
                                    @if($exc->status === 'OPEN')
                                        <form action="{{ route('exceptions.status.update', $exc) }}" method="POST" class="inline">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="status" value="INVESTIGATING">
                                            <button type="submit" class="px-2 py-1 rounded bg-amber-100 hover:bg-amber-200 text-amber-800 text-[10px] font-bold transition-colors">
                                                Investigate
                                            </button>
                                        </form>
                                    @endif

                                    @if($exc->status !== 'RESOLVED')
                                        <form action="{{ route('exceptions.status.update', $exc) }}" method="POST" class="inline">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="status" value="RESOLVED">
                                            <button type="submit" class="px-2 py-1 rounded bg-emerald-600 hover:bg-emerald-500 text-white text-[10px] font-bold shadow-sm transition-colors">
                                                Resolve
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="p-8 text-center text-xs text-slate-400 bg-slate-50 rounded-xl border border-dashed border-slate-200">
                <svg class="w-8 h-8 text-emerald-400 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>No active customs holds or operational disruptions reported for this consignment.</span>
            </div>
        @endif
    </div>

    <!-- Cargo Manifest Items Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
            <div>
                <h3 class="text-sm font-bold text-slate-900">Cargo Manifest Items</h3>
                <p class="text-xs text-slate-500">Declared packages, units, and volumetric properties</p>
            </div>
            <span class="text-xs text-slate-400 font-mono">{{ $shipment->items->count() }} line items</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-500 uppercase font-semibold text-[11px] border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-3">#</th>
                        <th class="px-6 py-3">Item Description</th>
                        <th class="px-6 py-3">SKU / Code</th>
                        <th class="px-6 py-3 text-right">Quantity</th>
                        <th class="px-6 py-3 text-right">Weight (kg)</th>
                        <th class="px-6 py-3 text-right">Volume (cbm)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse($shipment->items as $idx => $item)
                        <tr class="hover:bg-slate-50/70">
                            <td class="px-6 py-3.5 text-slate-400 font-mono">{{ $idx + 1 }}</td>
                            <td class="px-6 py-3.5 font-bold text-slate-900">{{ $item->item_name }}</td>
                            <td class="px-6 py-3.5 font-mono text-ocean-600">{{ $item->sku ?? '-' }}</td>
                            <td class="px-6 py-3.5 text-right font-semibold">{{ number_format($item->quantity) }}</td>
                            <td class="px-6 py-3.5 text-right font-mono">{{ number_format($item->weight, 2) }}</td>
                            <td class="px-6 py-3.5 text-right font-mono">{{ number_format($item->volume, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-6 text-center text-slate-400">No items listed.</td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot class="bg-slate-50/80 font-bold text-slate-900 text-xs border-t border-slate-200">
                    <tr>
                        <td colspan="3" class="px-6 py-3 text-right uppercase tracking-wider text-[11px] text-slate-500">Totals:</td>
                        <td class="px-6 py-3 text-right">{{ number_format($shipment->items->sum('quantity')) }} pcs</td>
                        <td class="px-6 py-3 text-right font-mono">{{ number_format($shipment->total_weight, 2) }} kg</td>
                        <td class="px-6 py-3 text-right font-mono">{{ number_format($shipment->total_volume, 2) }} cbm</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <!-- Maritime Route Overview Card -->
    <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm">
        <h3 class="text-sm font-bold text-slate-900 mb-2 flex items-center space-x-2">
            <svg class="w-4 h-4 text-ocean-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/></svg>
            <span>Maritime Navigation Corridor Reference</span>
        </h3>
        <p class="text-xs text-slate-500 mb-4">Corridor: {{ config('cargotrace.primary_corridor') }} &bull; Standard Transit Coordinates</p>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs font-mono">
            <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200">
                <div class="text-[10px] text-slate-400 uppercase font-sans font-bold">Departure Terminal</div>
                <div class="text-slate-900 font-bold mt-0.5">{{ $shipment->originLocation->name }} ({{ $shipment->originLocation->code }})</div>
                <div class="text-slate-500 text-[11px] mt-1">GPS: {{ $shipment->originLocation->latitude }}, {{ $shipment->originLocation->longitude }}</div>
            </div>

            <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200">
                <div class="text-[10px] text-slate-400 uppercase font-sans font-bold">Arrival Terminal</div>
                <div class="text-slate-900 font-bold mt-0.5">{{ $shipment->destinationLocation->name }} ({{ $shipment->destinationLocation->code }})</div>
                <div class="text-slate-500 text-[11px] mt-1">GPS: {{ $shipment->destinationLocation->latitude }}, {{ $shipment->destinationLocation->longitude }}</div>
            </div>
        </div>
    </div>

</div>

<!-- Recalibrate ETA Modal (Admin & Operator) -->
@if(auth()->user()->isAdmin() || auth()->user()->isOperator())
<div id="etaModal" class="fixed inset-0 z-50 hidden bg-slate-950/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-slate-200 animate-in fade-in zoom-in-95 duration-150">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-4">
            <div>
                <h3 class="text-base font-bold text-slate-900">Recalibrate Estimated Arrival</h3>
                <p class="text-xs text-slate-500">Calculate delay variance against contracted ETA</p>
            </div>
            <button type="button" onclick="closeEtaModal()" class="text-slate-400 hover:text-slate-600 p-1">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <form action="{{ route('shipments.eta.update', $shipment) }}" method="POST" class="space-y-4 text-xs">
            @csrf

            <div class="p-3 rounded-lg bg-slate-50 border border-slate-200 text-slate-600">
                <div>Expected Arrival: <strong class="text-slate-900">{{ $shipment->expected_arrival->format('d M Y - H:i') }} UTC</strong></div>
                <div class="mt-0.5">Tolerance Threshold: <strong class="text-slate-900">{{ $thresholdHours }} Hours</strong></div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">New Estimated Arrival (ETA) *</label>
                <input type="datetime-local" name="estimated_arrival" required
                    value="{{ $shipment->estimated_arrival ? $shipment->estimated_arrival->format('Y-m-d\TH:i') : $shipment->expected_arrival->format('Y-m-d\TH:i') }}"
                    class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-lg text-xs focus:bg-white focus:outline-none focus:ring-2 focus:ring-ocean-500">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Delay Reason (if delayed)</label>
                <textarea name="delay_reason" rows="3" placeholder="e.g. Weather disruption in Bay of Bengal or Port congestion at Suez"
                    class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-lg text-xs focus:bg-white focus:outline-none focus:ring-2 focus:ring-ocean-500">{{ $shipment->delay_reason }}</textarea>
            </div>

            <div class="flex items-center justify-end space-x-3 pt-3 border-t border-slate-100">
                <button type="button" onclick="closeEtaModal()" class="px-4 py-2 border border-slate-200 text-slate-700 rounded-lg text-xs font-semibold hover:bg-slate-50">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-ocean-600 hover:bg-ocean-500 text-white rounded-lg text-xs font-semibold shadow-sm">Save & Evaluate</button>
            </div>
        </form>
    </div>
</div>

<!-- Report Exception Modal (Admin & Operator) -->
<div id="exceptionModal" class="fixed inset-0 z-50 hidden bg-slate-950/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-slate-200 animate-in fade-in zoom-in-95 duration-150">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-4">
            <div>
                <h3 class="text-base font-bold text-slate-900">Report Operational Incident / Exception</h3>
                <p class="text-xs text-slate-500">Record a customs hold, port congestion, or risk event</p>
            </div>
            <button type="button" onclick="closeExceptionModal()" class="text-slate-400 hover:text-slate-600 p-1">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <form action="{{ route('shipments.exceptions.store', $shipment) }}" method="POST" class="space-y-4 text-xs">
            @csrf

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Disruption Type *</label>
                    <select name="exception_type" required class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-lg text-xs font-bold text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-ocean-500">
                        @foreach($exceptionTypes as $type)
                            <option value="{{ $type }}" {{ $type === 'CUSTOMS_HOLD' ? 'selected' : '' }}>
                                {{ $type }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Severity Rating *</label>
                    <select name="severity" required class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-lg text-xs font-bold text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-ocean-500">
                        <option value="LOW">LOW</option>
                        <option value="MEDIUM" selected>MEDIUM</option>
                        <option value="HIGH">HIGH</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Checkpoint / Location Name</label>
                <input type="text" name="location_name" placeholder="e.g. Suez Anchorage / Port Customs Gateway"
                    class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-lg text-xs focus:bg-white focus:outline-none focus:ring-2 focus:ring-ocean-500">
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Latitude</label>
                    <input type="number" step="0.0000001" name="latitude" placeholder="e.g. 29.9500"
                        class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-lg text-xs font-mono focus:bg-white focus:outline-none focus:ring-2 focus:ring-ocean-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Longitude</label>
                    <input type="number" step="0.0000001" name="longitude" placeholder="e.g. 32.5500"
                        class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-lg text-xs font-mono focus:bg-white focus:outline-none focus:ring-2 focus:ring-ocean-500">
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Incident Description & Root Cause *</label>
                <textarea name="description" rows="3" required placeholder="Specify documentation audit, quarantine hold, or physical inspection details..."
                    class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-lg text-xs focus:bg-white focus:outline-none focus:ring-2 focus:ring-ocean-500"></textarea>
            </div>

            <div class="flex items-center justify-end space-x-3 pt-3 border-t border-slate-100">
                <button type="button" onclick="closeExceptionModal()" class="px-4 py-2 border border-slate-200 text-slate-700 rounded-lg text-xs font-semibold hover:bg-slate-50">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-amber-600 hover:bg-amber-500 text-white rounded-lg text-xs font-bold shadow-sm">Report Exception</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openEtaModal() {
        document.getElementById('etaModal').classList.remove('hidden');
    }
    function closeEtaModal() {
        document.getElementById('etaModal').classList.add('hidden');
    }
    function openExceptionModal() {
        document.getElementById('exceptionModal').classList.remove('hidden');
    }
    function closeExceptionModal() {
        document.getElementById('exceptionModal').classList.add('hidden');
    }
</script>
@endif
@endsection
