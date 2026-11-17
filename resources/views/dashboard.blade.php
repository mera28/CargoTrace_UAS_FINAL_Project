@extends('layouts.app')

@section('title', 'Dashboard')
@section('header-title', auth()->user()->isCustomer() ? 'Client Consignment Portal' : 'Global Logistics Operations')
@section('header-subtitle', auth()->user()->isCustomer() ? 'Live status & milestones for your maritime shipments' : 'Real-time Southeast Asia → Europe Corridor Analytics & Risk Control')

@section('content')
<div class="space-y-6">

    <!-- Welcome Hero Card -->
    <div class="bg-gradient-to-r from-navy-900 via-navy-800 to-slate-900 rounded-2xl p-6 text-white shadow-lg border border-slate-800 relative overflow-hidden">
        <div class="absolute -right-10 -bottom-10 w-72 h-72 bg-ocean-500/10 rounded-full blur-3xl pointer-events-none"></div>

        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 relative z-0">
            <div>
                <div class="flex items-center space-x-2.5 mb-2">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-ocean-500/20 text-ocean-300 border border-ocean-500/30">
                        {{ strtoupper(auth()->user()->role) }} PORTAL
                    </span>
                    <span class="text-xs text-slate-400">&bull;</span>
                    <span class="text-xs text-slate-300 font-semibold">{{ auth()->user()->company_name ?? 'Individual Shipper' }}</span>
                </div>
                <h2 class="text-2xl font-black tracking-tight">
                    Welcome back, {{ auth()->user()->name }}!
                </h2>
                <p class="text-xs text-slate-300 mt-1 max-w-2xl leading-relaxed">
                    @if(auth()->user()->isAdmin())
                        You have master administrative authority across global carriers, port locations, consignment life cycles, and audit logs.
                    @elseif(auth()->user()->isOperator())
                        You have operational authority to manage waypoint milestones, recalibrate ETAs, and resolve customs hold exceptions.
                    @else
                        Track your consignments along the Southeast Asia &rarr; Europe corridor with real-time milestone positions and ETA intelligence.
                    @endif
                </p>
            </div>

            <!-- Active Corridor Status Pill -->
            <div class="bg-slate-800/80 border border-slate-700/80 rounded-xl p-3.5 flex items-center space-x-3 text-xs self-start md:self-auto shadow-sm">
                <div class="w-10 h-10 rounded-lg bg-emerald-500/20 text-emerald-400 flex items-center justify-center font-bold">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                </div>
                <div>
                    <div class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">Operational Corridor</div>
                    <div class="text-slate-100 font-bold mt-0.5">{{ config('cargotrace.primary_corridor') }}</div>
                </div>
            </div>
        </div>
    </div>

    @if(auth()->user()->isAdmin() || auth()->user()->isOperator())
        <!-- ========================================== -->
        <!-- ADMIN & OPERATOR GLOBAL OPERATIONS DASHBOARD -->
        <!-- ========================================== -->

        <!-- 5 Top KPI Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
            <!-- 1. Total Shipments -->
            <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-sm flex items-center space-x-4">
                <div class="w-12 h-12 rounded-xl bg-ocean-50 text-ocean-600 flex items-center justify-center flex-shrink-0">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                </div>
                <div class="min-w-0">
                    <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Total Shipments</div>
                    <div class="text-2xl font-black text-slate-900 mt-0.5">{{ number_format($totalShipments) }}</div>
                    <div class="text-[10px] text-slate-500 mt-0.5">Global Consignments</div>
                </div>
            </div>

            <!-- 2. In Transit -->
            <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-sm flex items-center space-x-4">
                <div class="w-12 h-12 rounded-xl bg-sky-50 text-sky-600 flex items-center justify-center flex-shrink-0">
                    <svg class="w-6 h-6 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                </div>
                <div class="min-w-0">
                    <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">In Transit</div>
                    <div class="text-2xl font-black text-sky-600 mt-0.5">{{ number_format($inTransitCount) }}</div>
                    <div class="text-[10px] text-slate-500 mt-0.5">Active at Sea / Air</div>
                </div>
            </div>

            <!-- 3. Delivered -->
            <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-sm flex items-center space-x-4">
                <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center flex-shrink-0">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                </div>
                <div class="min-w-0">
                    <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Delivered</div>
                    <div class="text-2xl font-black text-emerald-600 mt-0.5">{{ number_format($deliveredCount) }}</div>
                    <div class="text-[10px] text-slate-500 mt-0.5">Discharged at Port</div>
                </div>
            </div>

            <!-- 4. Delayed & Exceptions -->
            <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-sm flex items-center space-x-4">
                <div class="w-12 h-12 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center flex-shrink-0">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </div>
                <div class="min-w-0">
                    <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Delayed / Issues</div>
                    <div class="text-2xl font-black text-rose-600 mt-0.5">{{ number_format($delayedCount) }}</div>
                    <div class="text-[10px] text-slate-500 mt-0.5">{{ $openExceptionsCount }} Open Exceptions</div>
                </div>
            </div>

            <!-- 5. Primary KPI Highlight Card: On-Time Delivery Rate -->
            <div class="bg-gradient-to-br from-ocean-600 to-sky-700 rounded-2xl p-5 text-white shadow-md shadow-ocean-600/20 flex items-center space-x-4">
                <div class="w-12 h-12 rounded-xl bg-white/20 text-white flex items-center justify-center flex-shrink-0">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                </div>
                <div class="min-w-0">
                    <div class="text-[11px] font-bold text-sky-200 uppercase tracking-wider">On-Time Rate</div>
                    <div class="text-2xl font-black text-white mt-0.5">{{ $onTimeRate }}%</div>
                    <div class="text-[10px] text-sky-100 mt-0.5">Primary KPI Target</div>
                </div>
            </div>
        </div>

        <!-- 2 Interactive Analytics Charts (Chart.js) -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Chart 1: Status Distribution Doughnut -->
            <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <div>
                        <h3 class="text-sm font-bold text-slate-900 flex items-center space-x-2">
                            <svg class="w-4 h-4 text-ocean-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z"/></svg>
                            <span>Shipments by Current Status</span>
                        </h3>
                        <p class="text-xs text-slate-500">Live operational distribution across lifecycle phases</p>
                    </div>
                    <span class="text-xs font-mono font-bold text-slate-400">{{ $totalShipments }} Total</span>
                </div>

                <div class="relative h-64 flex items-center justify-center">
                    <canvas id="statusChart"></canvas>
                </div>
            </div>

            <!-- Chart 2: Shipments by Carrier Bar Chart -->
            <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <div>
                        <h3 class="text-sm font-bold text-slate-900 flex items-center space-x-2">
                            <svg class="w-4 h-4 text-ocean-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                            <span>Carrier Volume Distribution</span>
                        </h3>
                        <p class="text-xs text-slate-500">Active consignment volume per contracted ocean carrier</p>
                    </div>
                    <span class="text-xs font-mono font-bold text-slate-400">{{ count($carrierLabels) }} Carriers</span>
                </div>

                <div class="relative h-64 flex items-center justify-center">
                    <canvas id="carrierChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Priority Action Items (Exceptions & Delays) -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden space-y-0">
            <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-bold text-slate-900 flex items-center space-x-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-rose-500 animate-ping"></span>
                        <span>Priority Operational Action Items</span>
                    </h3>
                    <p class="text-xs text-slate-500">Consignments requiring immediate operator attention (Delays &ge; {{ config('cargotrace.delay_threshold_hours', 12) }}h or Open Customs Holds)</p>
                </div>
                <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-rose-100 text-rose-800">
                    {{ $priorityShipments->count() }} Issues Pending
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 text-slate-500 font-semibold uppercase tracking-wider border-b border-slate-200/80">
                        <tr>
                            <th class="px-6 py-3.5">Consignment No.</th>
                            <th class="px-6 py-3.5">Shipper Client</th>
                            <th class="px-6 py-3.5">Corridor Route</th>
                            <th class="px-6 py-3.5">Carrier</th>
                            <th class="px-6 py-3.5">Delay & Severity</th>
                            <th class="px-6 py-3.5">Active Incidents</th>
                            <th class="px-6 py-3.5 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700">
                        @forelse($priorityShipments as $shipment)
                            @php
                                $openExcs = $shipment->exceptions->whereIn('status', ['OPEN', 'INVESTIGATING']);
                            @endphp
                            <tr class="hover:bg-slate-50/70 transition-colors">
                                <td class="px-6 py-4 font-mono font-bold text-ocean-600">
                                    <a href="{{ route('shipments.show', $shipment) }}" class="hover:underline">
                                        {{ $shipment->shipment_number }}
                                    </a>
                                </td>
                                <td class="px-6 py-4 font-medium text-slate-900">
                                    <div>{{ $shipment->customer->name }}</div>
                                    <div class="text-[11px] text-slate-400">{{ $shipment->customer->company_name ?? 'Individual' }}</div>
                                </td>
                                <td class="px-6 py-4 text-slate-800 font-medium">
                                    {{ $shipment->originLocation->code }} &rarr; {{ $shipment->destinationLocation->code }}
                                    <div class="text-[11px] text-slate-400">{{ $shipment->originLocation->city }} to {{ $shipment->destinationLocation->city }}</div>
                                </td>
                                <td class="px-6 py-4 font-semibold text-slate-800">
                                    {{ $shipment->carrier->name }}
                                </td>
                                <td class="px-6 py-4">
                                    @if($shipment->is_delayed)
                                        <span class="inline-flex items-center space-x-1 px-2.5 py-1 rounded-full text-[11px] font-bold {{ $shipment->delay_severity === 'HIGH' ? 'bg-rose-100 text-rose-800 border border-rose-200' : 'bg-amber-100 text-amber-800 border border-amber-200' }}">
                                            <span>+{{ $shipment->delay_hours }}h ({{ $shipment->delay_severity }})</span>
                                        </span>
                                    @else
                                        <span class="text-slate-400 italic">No delay variance</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    @if($openExcs->count() > 0)
                                        <div class="space-y-1">
                                            @foreach($openExcs as $ex)
                                                <span class="inline-flex items-center space-x-1 px-2 py-0.5 rounded text-[10px] font-extrabold bg-rose-50 text-rose-700 border border-rose-200">
                                                    <span>{{ $ex->exception_type }}</span>
                                                    <span>({{ $ex->status }})</span>
                                                </span>
                                            @endforeach
                                        </div>
                                    @else
                                        <span class="text-slate-400 italic">None</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <a href="{{ route('shipments.show', $shipment) }}" class="inline-flex items-center space-x-1 px-3 py-1.5 rounded-lg bg-slate-900 hover:bg-slate-800 text-white font-semibold text-xs transition-colors">
                                        <span>Resolve</span>
                                        <svg class="w-3.5 h-3.5 text-ocean-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-8 text-center text-slate-400">
                                    <div class="flex flex-col items-center justify-center space-y-1">
                                        <svg class="w-8 h-8 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        <span class="font-bold text-slate-700">All Corridors Clear</span>
                                        <span class="text-xs text-slate-400">No active delay thresholds breached or open customs holds.</span>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    @else
        <!-- ========================================== -->
        <!-- CUSTOMER CLIENT SHIPMENT PORTAL -->
        <!-- ========================================== -->

        <!-- Active Exception Alert Banner (if customer has active exceptions) -->
        @if($alertExceptions->count() > 0)
            <div class="bg-gradient-to-r from-amber-500/10 via-rose-500/10 to-amber-500/10 border-2 border-amber-400/80 rounded-2xl p-5 shadow-sm space-y-3">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 rounded-xl bg-amber-500 text-white flex items-center justify-center flex-shrink-0 shadow-md">
                        <svg class="w-6 h-6 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-amber-900">Customs Clearance & Operational Alerts Active</h3>
                        <p class="text-xs text-amber-800 mt-0.5">We have detected operational holds or documentation inspections affecting {{ $alertExceptions->count() }} of your consignments.</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-3 pt-1">
                    @foreach($alertExceptions as $exc)
                        <div class="p-3 bg-white/90 rounded-xl border border-amber-200 text-xs flex items-center justify-between">
                            <div>
                                <span class="font-mono font-bold text-slate-900">{{ $exc->shipment->shipment_number }}</span>:
                                <span class="font-extrabold text-rose-700">{{ $exc->exception_type }}</span>
                                <div class="text-[11px] text-slate-500 mt-0.5">{{ Str::limit($exc->description, 60) }}</div>
                            </div>
                            <a href="{{ route('shipments.show', $exc->shipment) }}" class="px-3 py-1.5 rounded-lg bg-amber-600 hover:bg-amber-500 text-white font-bold text-xs transition-colors flex-shrink-0 ml-2">
                                Review Details
                            </a>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Customer Summary Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-sm flex items-center space-x-4">
                <div class="w-12 h-12 rounded-xl bg-ocean-50 text-ocean-600 flex items-center justify-center flex-shrink-0">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                </div>
                <div>
                    <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">My Consignments</div>
                    <div class="text-2xl font-black text-slate-900 mt-0.5">{{ number_format($totalShipments) }}</div>
                    <div class="text-[10px] text-slate-500 mt-0.5">Registered Shipments</div>
                </div>
            </div>

            <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-sm flex items-center space-x-4">
                <div class="w-12 h-12 rounded-xl bg-sky-50 text-sky-600 flex items-center justify-center flex-shrink-0">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                </div>
                <div>
                    <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">In Transit</div>
                    <div class="text-2xl font-black text-sky-600 mt-0.5">{{ number_format($inTransitCount) }}</div>
                    <div class="text-[10px] text-slate-500 mt-0.5">Currently En Route</div>
                </div>
            </div>

            <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-sm flex items-center space-x-4">
                <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center flex-shrink-0">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                </div>
                <div>
                    <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Delivered</div>
                    <div class="text-2xl font-black text-emerald-600 mt-0.5">{{ number_format($deliveredCount) }}</div>
                    <div class="text-[10px] text-slate-500 mt-0.5">Successfully Received</div>
                </div>
            </div>

            <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-sm flex items-center space-x-4">
                <div class="w-12 h-12 rounded-xl {{ $activeExceptionsCount > 0 ? 'bg-rose-50 text-rose-600' : 'bg-slate-50 text-slate-400' }} flex items-center justify-center flex-shrink-0">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </div>
                <div>
                    <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Active Alerts</div>
                    <div class="text-2xl font-black {{ $activeExceptionsCount > 0 ? 'text-rose-600' : 'text-slate-800' }} mt-0.5">{{ number_format($activeExceptionsCount) }}</div>
                    <div class="text-[10px] text-slate-500 mt-0.5">Customs / Inspections</div>
                </div>
            </div>
        </div>

        <!-- Customer Shipments Directory & Quick Map Links -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden space-y-0">
            <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-bold text-slate-900">My Consignments Status</h3>
                    <p class="text-xs text-slate-500">Live positions, estimated arrivals, and tracking timeline</p>
                </div>
                <span class="text-xs font-mono font-semibold text-slate-400">{{ $customerShipments->count() }} Packages</span>
            </div>

            <div class="divide-y divide-slate-100">
                @forelse($customerShipments as $cShip)
                    @php
                        $latestMilestone = $cShip->trackingEvents->first();
                    @endphp
                    <div class="p-6 hover:bg-slate-50/60 transition-colors flex flex-col lg:flex-row lg:items-center justify-between gap-6">
                        <div class="space-y-2 flex-1">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="font-mono text-sm font-black text-ocean-600 bg-ocean-50 px-2 py-0.5 rounded border border-ocean-200/60">
                                    {{ $cShip->shipment_number }}
                                </span>
                                <span class="text-xs text-slate-400">&bull;</span>
                                <span class="text-xs font-semibold text-slate-700">{{ $cShip->carrier->name }}</span>
                                <span class="text-xs text-slate-400">&bull;</span>
                                <span class="text-xs text-slate-500">{{ $cShip->cargo_type }}</span>

                                @if($cShip->current_status === 'DELIVERED')
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">DELIVERED</span>
                                @elseif($cShip->current_status === 'IN_TRANSIT')
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-sky-100 text-sky-800 flex items-center space-x-1">
                                        <span class="w-1.5 h-1.5 rounded-full bg-sky-500 animate-pulse"></span>
                                        <span>IN TRANSIT</span>
                                    </span>
                                @else
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-800">{{ $cShip->current_status }}</span>
                                @endif

                                @if($cShip->is_delayed)
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-900 border border-amber-200">
                                        Delayed +{{ $cShip->delay_hours }}h
                                    </span>
                                @endif
                            </div>

                            <div class="text-sm font-extrabold text-slate-900 flex items-center space-x-2">
                                <span>{{ $cShip->originLocation->name }}</span>
                                <span class="text-ocean-500">&rarr;</span>
                                <span>{{ $cShip->destinationLocation->name }}</span>
                            </div>

                            <div class="flex flex-wrap items-center gap-4 text-xs text-slate-500">
                                <div>
                                    <strong class="text-slate-700">Contracted ETA:</strong>
                                    {{ $cShip->expected_arrival->format('d M Y - H:i') }} UTC
                                </div>
                                @if($cShip->estimated_arrival)
                                    <div>
                                        <strong class="text-slate-700">Current Forecast:</strong>
                                        {{ $cShip->estimated_arrival->format('d M Y - H:i') }} UTC
                                    </div>
                                @endif
                            </div>

                            <!-- Latest Milestone Position -->
                            <div class="p-3 bg-slate-50 rounded-xl border border-slate-200/80 text-xs flex items-center space-x-2">
                                <svg class="w-4 h-4 text-ocean-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                <div>
                                    <span class="font-bold text-slate-800">Current Position:</span>
                                    <span>{{ $latestMilestone ? $latestMilestone->location_name : $cShip->originLocation->name }}</span>
                                    <span class="text-slate-400 font-mono text-[11px] ml-1">
                                        ({{ $latestMilestone ? $latestMilestone->event_time->diffForHumans() : 'Registered at origin' }})
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Track on Map Quick Button -->
                        <div class="flex lg:flex-col items-center justify-end gap-2 flex-shrink-0">
                            <a href="{{ route('shipments.show', $cShip) }}" class="inline-flex items-center space-x-2 px-4 py-2.5 rounded-xl bg-ocean-600 hover:bg-ocean-500 text-white font-bold text-xs shadow-md shadow-ocean-600/20 transition-all">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/></svg>
                                <span>Track on Map &rarr;</span>
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="p-12 text-center text-slate-400">
                        <p class="text-sm font-semibold text-slate-600">No Consignments Found</p>
                        <p class="text-xs text-slate-400 mt-1">You currently have no registered consignments in the CargoTrace system.</p>
                    </div>
                @endforelse
            </div>
        </div>
    @endif

</div>

@if(auth()->user()->isAdmin() || auth()->user()->isOperator())
    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Chart 1: Status Distribution Doughnut Chart
            const statusLabels = @json($statusLabels);
            const statusData = @json($statusData);

            const statusColors = {
                'CREATED': '#94a3b8',
                'BOOKED': '#a855f7',
                'PICKED_UP': '#6366f1',
                'IN_TRANSIT': '#0284c7',
                'DELIVERED': '#10b981'
            };

            const bgColors = statusLabels.map(st => statusColors[st] || '#64748b');

            const ctxStatus = document.getElementById('statusChart').getContext('2d');
            new Chart(ctxStatus, {
                type: 'doughnut',
                data: {
                    labels: statusLabels,
                    datasets: [{
                        data: statusData,
                        backgroundColor: bgColors,
                        borderWidth: 2,
                        borderColor: '#ffffff',
                        hoverOffset: 6
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '70%',
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                boxWidth: 12,
                                padding: 15,
                                font: {
                                    size: 11,
                                    family: "'Plus Jakarta Sans', sans-serif",
                                    weight: 600
                                }
                            }
                        }
                    }
                }
            });

            // Chart 2: Carrier Volume Bar Chart
            const carrierLabels = @json($carrierLabels);
            const carrierData = @json($carrierData);

            const ctxCarrier = document.getElementById('carrierChart').getContext('2d');
            new Chart(ctxCarrier, {
                type: 'bar',
                data: {
                    labels: carrierLabels,
                    datasets: [{
                        label: 'Shipments Handled',
                        data: carrierData,
                        backgroundColor: '#0284c7',
                        borderRadius: 8,
                        borderSkipped: false,
                        barThickness: 24
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                stepSize: 1,
                                font: {
                                    family: "'Plus Jakarta Sans', sans-serif",
                                    size: 10
                                }
                            },
                            grid: {
                                color: '#f1f5f9'
                            }
                        },
                        x: {
                            ticks: {
                                font: {
                                    family: "'Plus Jakarta Sans', sans-serif",
                                    size: 10,
                                    weight: 600
                                }
                            },
                            grid: {
                                display: false
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: false
                        }
                    }
                }
            });
        });
    </script>
    @endpush
@endif
@endsection
