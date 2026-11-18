@extends(auth()->user()->isCustomer() ? 'layouts.customer' : 'layouts.app')

@section('title', auth()->user()->isCustomer() ? 'Global Tracking Portal' : 'Logistics Operations Dashboard')
@section('header-title', auth()->user()->isCustomer() ? 'Client Consignment Portal' : 'Global Logistics Operations')
@section('header-subtitle', auth()->user()->isCustomer() ? 'Live status & milestones for your maritime shipments' : 'Real-time Southeast Asia → Europe Corridor Analytics & Risk Control')

@section('content')

@if(auth()->user()->isAdmin() || auth()->user()->isOperator())
    <!-- ======================================================== -->
    <!-- ADMIN & OPERATOR GLOBAL OPERATIONS DASHBOARD (SIDEBAR) -->
    <!-- ======================================================== -->
    <div class="space-y-6">

        <!-- Welcome Hero Card -->
        <div class="bg-gradient-to-r from-navy-900 via-navy-800 to-slate-900 rounded-2xl p-6 text-white shadow-lg border border-slate-800 relative overflow-hidden">
            <div class="absolute -right-10 -bottom-10 w-72 h-72 bg-ocean-500/10 rounded-full blur-3xl pointer-events-none"></div>

            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 relative z-0">
                <div>
                    <div class="flex items-center space-x-2.5 mb-2">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-ocean-500/20 text-ocean-300 border border-ocean-500/30">
                            {{ strtoupper(auth()->user()->role) }} CONTROL
                        </span>
                        <span class="text-xs text-slate-400">&bull;</span>
                        <span class="text-xs text-slate-300 font-semibold">{{ auth()->user()->company_name ?? 'Operations HQ' }}</span>
                    </div>
                    <h2 class="text-2xl font-black tracking-tight">
                        Welcome back, {{ auth()->user()->name }}!
                    </h2>
                    <p class="text-xs text-slate-300 mt-1 max-w-2xl leading-relaxed">
                        @if(auth()->user()->isAdmin())
                            You have master administrative authority across global carriers, port locations, consignment life cycles, and audit logs.
                        @else
                            You have operational authority to manage waypoint milestones, recalibrate ETAs, and resolve customs hold exceptions.
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

        <!-- 5 Top KPI Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
            <!-- 1. Total Shipments -->
            <div class="bg-white rounded-2xl p-5 border border-slate-300 shadow-md flex items-center space-x-4">
                <div class="w-12 h-12 rounded-xl bg-ocean-50 text-ocean-600 flex items-center justify-center flex-shrink-0 border border-ocean-100">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                </div>
                <div class="min-w-0">
                    <div class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Total Shipments</div>
                    <div class="text-2xl font-black text-slate-900 mt-0.5">{{ number_format($totalShipments) }}</div>
                    <div class="text-[10px] text-slate-500 mt-0.5">Global Consignments</div>
                </div>
            </div>

            <!-- 2. In Transit -->
            <div class="bg-white rounded-2xl p-5 border border-slate-300 shadow-md flex items-center space-x-4">
                <div class="w-12 h-12 rounded-xl bg-sky-50 text-sky-600 flex items-center justify-center flex-shrink-0 border border-sky-100">
                    <svg class="w-6 h-6 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                </div>
                <div class="min-w-0">
                    <div class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">In Transit</div>
                    <div class="text-2xl font-black text-sky-600 mt-0.5">{{ number_format($inTransitCount) }}</div>
                    <div class="text-[10px] text-slate-500 mt-0.5">Active at Sea / Air</div>
                </div>
            </div>

            <!-- 3. Delivered -->
            <div class="bg-white rounded-2xl p-5 border border-slate-300 shadow-md flex items-center space-x-4">
                <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center flex-shrink-0 border border-emerald-100">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                </div>
                <div class="min-w-0">
                    <div class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Delivered</div>
                    <div class="text-2xl font-black text-emerald-600 mt-0.5">{{ number_format($deliveredCount) }}</div>
                    <div class="text-[10px] text-slate-500 mt-0.5">Discharged at Port</div>
                </div>
            </div>

            <!-- 4. Delayed & Exceptions -->
            <div class="bg-white rounded-2xl p-5 border border-slate-300 shadow-md flex items-center space-x-4">
                <div class="w-12 h-12 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center flex-shrink-0 border border-rose-100">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </div>
                <div class="min-w-0">
                    <div class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Delayed / Issues</div>
                    <div class="text-2xl font-black text-rose-600 mt-0.5">{{ number_format($delayedCount) }}</div>
                    <div class="text-[10px] text-slate-500 mt-0.5">{{ $openExceptionsCount }} Open Exceptions</div>
                </div>
            </div>

            <!-- 5. Primary KPI Highlight Card: On-Time Delivery Rate -->
            <div class="bg-gradient-to-br from-ocean-600 to-sky-700 rounded-2xl p-5 text-white shadow-md shadow-ocean-600/20 flex items-center space-x-4">
                <div class="w-12 h-12 rounded-xl bg-white/20 text-white flex items-center justify-center flex-shrink-0 border border-white/20">
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
            <div class="bg-white rounded-2xl border border-slate-300 p-6 shadow-md space-y-4">
                <div class="flex items-center justify-between border-b border-slate-200 pb-3">
                    <div>
                        <h3 class="text-sm font-bold text-slate-900 flex items-center space-x-2">
                            <svg class="w-4 h-4 text-ocean-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z"/></svg>
                            <span>Shipments by Current Status</span>
                        </h3>
                        <p class="text-xs text-slate-500">Live operational distribution across lifecycle phases</p>
                    </div>
                    <span class="text-xs font-mono font-bold text-slate-500">{{ $totalShipments }} Total</span>
                </div>

                <div class="relative h-64 flex items-center justify-center">
                    <canvas id="statusChart"></canvas>
                </div>
            </div>

            <!-- Chart 2: Shipments by Carrier Bar Chart -->
            <div class="bg-white rounded-2xl border border-slate-300 p-6 shadow-md space-y-4">
                <div class="flex items-center justify-between border-b border-slate-200 pb-3">
                    <div>
                        <h3 class="text-sm font-bold text-slate-900 flex items-center space-x-2">
                            <svg class="w-4 h-4 text-ocean-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                            <span>Carrier Volume Distribution</span>
                        </h3>
                        <p class="text-xs text-slate-500">Active consignment volume per contracted ocean carrier</p>
                    </div>
                    <span class="text-xs font-mono font-bold text-slate-500">{{ count($carrierLabels) }} Carriers</span>
                </div>

                <div class="relative h-64 flex items-center justify-center">
                    <canvas id="carrierChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Priority Action Items (Exceptions & Delays) -->
        <div class="bg-white rounded-2xl border border-slate-300 shadow-md overflow-hidden space-y-0">
            <div class="px-6 py-4 border-b border-slate-200 flex items-center justify-between bg-slate-50/70">
                <div>
                    <h3 class="text-sm font-bold text-slate-900 flex items-center space-x-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-rose-500 animate-ping"></span>
                        <span>Priority Operational Action Items</span>
                    </h3>
                    <p class="text-xs text-slate-500">Consignments requiring immediate operator attention (Delays &ge; {{ config('cargotrace.delay_threshold_hours', 12) }}h or Open Customs Holds)</p>
                </div>
                <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-rose-100 text-rose-800 border border-rose-200">
                    {{ $priorityShipments->count() }} Issues Pending
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-900 text-white font-semibold uppercase tracking-wider text-[11px]">
                        <tr>
                            <th class="px-6 py-4 text-white whitespace-nowrap">Consignment No.</th>
                            <th class="px-6 py-4 text-white">Shipper Client</th>
                            <th class="px-6 py-4 text-white whitespace-nowrap">Corridor Route</th>
                            <th class="px-6 py-4 text-white whitespace-nowrap">Carrier</th>
                            <th class="px-6 py-4 text-white whitespace-nowrap">Delay & Severity</th>
                            <th class="px-6 py-4 text-white">Active Incidents</th>
                            <th class="px-6 py-4 text-right text-white whitespace-nowrap">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 text-slate-700 bg-white">
                        @forelse($priorityShipments as $shipment)
                            @php
                                $openExcs = $shipment->exceptions->whereIn('status', ['OPEN', 'INVESTIGATING']);
                            @endphp
                            <tr class="hover:bg-sky-50 even:bg-slate-50/80 transition-colors">
                                <td class="px-6 py-4 font-mono font-bold whitespace-nowrap">
                                    <a href="{{ route('shipments.show', $shipment) }}" class="text-ocean-600 hover:text-ocean-700 hover:underline flex items-center space-x-1">
                                        <span>{{ $shipment->shipment_number }}</span>
                                        <svg class="w-3.5 h-3.5 text-ocean-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                    </a>
                                </td>
                                <td class="px-6 py-4 font-medium text-slate-900">
                                    <div class="font-bold">{{ $shipment->customer->name }}</div>
                                    <div class="text-[11px] text-slate-500">{{ $shipment->customer->company_name ?? 'Individual' }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center space-x-1.5 font-semibold text-slate-800">
                                        <span class="px-2 py-0.5 rounded bg-slate-100 border border-slate-300 text-slate-800 font-mono font-bold text-[11px]">{{ $shipment->originLocation->code }}</span>
                                        <span class="text-ocean-600 font-bold">&rarr;</span>
                                        <span class="px-2 py-0.5 rounded bg-slate-100 border border-slate-300 text-slate-800 font-mono font-bold text-[11px]">{{ $shipment->destinationLocation->code }}</span>
                                    </div>
                                    <div class="text-[10px] text-slate-500 mt-1">{{ $shipment->originLocation->city }} to {{ $shipment->destinationLocation->city }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="font-semibold text-slate-900">{{ $shipment->carrier->name }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($shipment->is_delayed)
                                        <span class="inline-flex items-center space-x-1 px-2.5 py-1 rounded-lg text-[11px] font-bold border {{ $shipment->delay_severity === 'HIGH' ? 'bg-rose-100 text-rose-800 border-rose-200' : 'bg-amber-100 text-amber-800 border-amber-200' }}">
                                            <span>+{{ $shipment->delay_hours }}h ({{ $shipment->delay_severity }})</span>
                                        </span>
                                    @else
                                        <span class="text-emerald-700 font-bold">On Schedule</span>
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
                                <td class="px-6 py-4 text-right whitespace-nowrap">
                                    <a href="{{ route('shipments.show', $shipment) }}" class="inline-flex items-center space-x-1 px-3 py-1.5 rounded-lg bg-slate-900 hover:bg-slate-800 text-white font-semibold text-xs transition-colors shadow-xs">
                                        <span>Resolve</span>
                                        <svg class="w-3.5 h-3.5 text-ocean-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-12 text-center text-slate-400">
                                    <div class="flex flex-col items-center justify-center space-y-1">
                                        <svg class="w-8 h-8 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        <span class="font-bold text-slate-700">All Corridors Clear</span>
                                        <span class="text-xs text-slate-500">No active delay thresholds breached or open customs holds.</span>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>

@else
    <!-- ======================================================== -->
    <!-- CUSTOMER CLIENT TRACKING PORTAL (MODERN WEBSITE LAYOUT) -->
    <!-- ======================================================== -->

    <!-- HERO SECTION: 3D Animated Globe & Real-time Quick Tracking -->
    <section class="relative bg-gradient-to-b from-navy-950 via-[#0B132B] to-navy-900 text-white overflow-hidden py-10 lg:py-16 border-b border-slate-800/80 shadow-2xl">
        <!-- Glowing background radial effect -->
        <div class="absolute top-1/4 left-1/4 w-96 h-96 bg-ocean-600/15 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute bottom-0 right-10 w-96 h-96 bg-sky-500/10 rounded-full blur-3xl pointer-events-none"></div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-center">

                <!-- Left Column: Search & Summary Telemetry -->
                <div class="lg:col-span-6 space-y-6">
                    <div class="inline-flex items-center space-x-2 px-3 py-1 rounded-full bg-ocean-500/15 text-ocean-400 border border-ocean-500/30 text-[11px] font-extrabold uppercase tracking-wider">
                        <span class="w-2 h-2 rounded-full bg-ocean-400 animate-pulse"></span>
                        <span>Maritime Corridor &bull; Southeast Asia &rarr; Europe</span>
                    </div>

                    <h1 class="text-3xl sm:text-4xl lg:text-5xl font-black text-white tracking-tight leading-tight">
                        Global Cargo Visibility, <span class="text-transparent bg-clip-text bg-gradient-to-r from-sky-400 via-ocean-400 to-sky-300">Simplified.</span>
                    </h1>

                    <p class="text-slate-300 text-xs sm:text-sm leading-relaxed max-w-xl">
                        Real-time voyage tracking, predictive ETA intelligence, and automated customs clearance alerts across the Southeast Asia &rarr; Europe maritime corridor.
                    </p>

                    <!-- Interactive Quick Tracking Search Box -->
                    <form action="{{ route('shipments.index') }}" method="GET" class="relative max-w-xl group">
                        <div class="relative flex items-center bg-slate-900/90 border border-slate-700/80 rounded-2xl shadow-2xl p-1.5 focus-within:border-ocean-500 focus-within:ring-2 focus-within:ring-ocean-500/30 transition-all">
                            <div class="pl-3.5 pr-2 text-slate-400">
                                <svg class="w-5 h-5 text-ocean-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                            </div>
                            <input type="text" name="search" placeholder="Enter Consignment Number (e.g. CT-2026-001)..."
                                class="w-full bg-transparent border-none text-xs sm:text-sm text-white placeholder-slate-400 focus:outline-none py-2 font-mono">
                            <button type="submit"
                                class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-ocean-600 to-sky-500 hover:from-ocean-500 hover:to-sky-400 text-white font-bold text-xs sm:text-sm shadow-md shadow-ocean-600/30 transition-all flex items-center space-x-1.5 flex-shrink-0">
                                <span>Track</span>
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                            </button>
                        </div>
                    </form>

                    <!-- Quick Track Suggestion Pills -->
                    @if($customerShipments->count() > 0)
                        <div class="flex items-center space-x-2 text-xs">
                            <span class="text-slate-400 text-[11px]">Your consignments:</span>
                            <div class="flex flex-wrap gap-1.5">
                                @foreach($customerShipments->take(3) as $qs)
                                    <a href="{{ route('shipments.show', $qs) }}"
                                       class="text-[11px] font-mono font-bold px-2.5 py-0.5 rounded-lg bg-slate-800/90 hover:bg-ocean-600/40 text-sky-300 border border-slate-700/80 transition-colors">
                                        {{ $qs->shipment_number }}
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <!-- 3 Mini Metric Cards (Glassmorphism) -->
                    <div class="grid grid-cols-3 gap-3 pt-2 max-w-xl">
                        <!-- Active Shipments -->
                        <div class="p-3.5 rounded-2xl bg-slate-800/40 border border-slate-700/60 backdrop-blur-md shadow-inner">
                            <div class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">In Transit</div>
                            <div class="text-xl sm:text-2xl font-black text-sky-400 mt-0.5">{{ number_format($inTransitCount) }}</div>
                            <div class="text-[10px] text-slate-400 mt-0.5 font-medium">Currently En Route</div>
                        </div>

                        <!-- Delivered -->
                        <div class="p-3.5 rounded-2xl bg-slate-800/40 border border-slate-700/60 backdrop-blur-md shadow-inner">
                            <div class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">Delivered</div>
                            <div class="text-xl sm:text-2xl font-black text-emerald-400 mt-0.5">{{ number_format($deliveredCount) }}</div>
                            <div class="text-[10px] text-slate-400 mt-0.5 font-medium">Discharged at Port</div>
                        </div>

                        <!-- Attention Needed -->
                        <div class="p-3.5 rounded-2xl bg-slate-800/40 border border-slate-700/60 backdrop-blur-md shadow-inner">
                            <div class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">Attention</div>
                            <div class="text-xl sm:text-2xl font-black {{ $activeExceptionsCount > 0 ? 'text-rose-400' : 'text-slate-300' }} mt-0.5">{{ number_format($activeExceptionsCount) }}</div>
                            <div class="text-[10px] text-slate-400 mt-0.5 font-medium">Customs / Holds</div>
                        </div>
                    </div>
                </div>

                <!-- Right Column: 3D Interactive Animated Globe -->
                @php
                    $latestActiveShipment = $customerShipments->where('current_status', '!=', 'DELIVERED')->first() ?? $customerShipments->first();
                @endphp
                <div class="lg:col-span-6 relative flex items-center justify-center">
                    <div class="relative w-full h-[380px] sm:h-[440px] lg:h-[480px] rounded-3xl overflow-hidden flex items-center justify-center">
                        <!-- 3D Globe Container -->
                        <div id="globeContainer" class="w-full h-full relative cursor-grab active:cursor-grabbing flex items-center justify-center">
                            <!-- Fallback Radar Illustration if WebGL is unavailable -->
                            <div id="globeFallback" class="hidden flex-col items-center justify-center space-y-3 p-8 text-center text-slate-400">
                                <div class="w-20 h-20 rounded-full border-2 border-dashed border-ocean-400/50 flex items-center justify-center animate-spin">
                                    <div class="w-12 h-12 rounded-full border-2 border-ocean-500/40"></div>
                                </div>
                                <div class="text-sm font-bold text-slate-200">Southeast Asia &rarr; Europe Corridor</div>
                                <div class="text-[11px] text-slate-400">Rotterdam &bull; Hamburg &bull; Antwerp &bull; Singapore &bull; Jakarta</div>
                            </div>
                        </div>

                        <!-- 1. Floating Live Courier / Vessel Telemetry Glassmorphism Widget -->
                        @if($latestActiveShipment)
                            <a href="{{ route('shipments.show', $latestActiveShipment) }}"
                               class="group absolute top-4 left-4 z-20 max-w-[270px] sm:max-w-xs p-3 sm:p-3.5 rounded-2xl bg-slate-900/80 backdrop-blur-md border border-sky-400/30 text-white shadow-2xl hover:border-sky-400 hover:bg-slate-900/90 transition-all pointer-events-auto">
                                <div class="flex items-center justify-between gap-2 mb-1.5">
                                    <div class="flex items-center space-x-1.5">
                                        <span class="w-2 h-2 rounded-full bg-emerald-400 animate-ping"></span>
                                        <span class="text-[10px] font-mono font-bold uppercase tracking-wider text-emerald-400">Live Corridor Active</span>
                                    </div>
                                    <span class="text-[9px] font-mono font-bold px-1.5 py-0.5 rounded bg-ocean-500/20 text-sky-300 border border-sky-500/30">
                                        {{ $latestActiveShipment->current_status }}
                                    </span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <div class="min-w-0 pr-2">
                                        <div class="font-mono font-black text-sm text-sky-300 group-hover:text-white transition-colors truncate">
                                            {{ $latestActiveShipment->shipment_number }}
                                        </div>
                                        <div class="text-[11px] text-slate-300 font-semibold flex items-center space-x-1.5 mt-0.5">
                                            <span class="px-1.5 py-0.2 rounded bg-slate-800 border border-slate-700 text-sky-300 font-mono text-[10px]">{{ $latestActiveShipment->originLocation->code }}</span>
                                            <span class="text-ocean-400">&rarr;</span>
                                            <span class="px-1.5 py-0.2 rounded bg-slate-800 border border-slate-700 text-sky-300 font-mono text-[10px]">{{ $latestActiveShipment->destinationLocation->code }}</span>
                                            <span class="text-slate-400 font-normal text-[10px] truncate">({{ $latestActiveShipment->carrier->name }})</span>
                                        </div>
                                    </div>
                                    <div class="w-7 h-7 rounded-lg bg-sky-500/20 text-sky-400 flex items-center justify-center group-hover:bg-ocean-600 group-hover:text-white transition-all flex-shrink-0">
                                        <svg class="w-4 h-4 group-hover:translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                                    </div>
                                </div>
                            </a>
                        @endif

                        <!-- Floating HUD Badge -->
                        <div class="absolute bottom-4 right-4 px-3 py-1.5 rounded-full bg-slate-900/80 border border-slate-700/80 backdrop-blur-md text-[10px] font-mono text-sky-300 flex items-center space-x-2 shadow-xl pointer-events-none">
                            <span class="w-2 h-2 rounded-full bg-sky-400 animate-ping"></span>
                            <span>3D LIVE MARITIME CORRIDOR &bull; INTERACTIVE</span>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- MAIN CLIENT SECTION: Consignment Directory & Visual Progress Tracking -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-10 space-y-8">

        <!-- Active Exception Alert Banner (if customer has active exceptions) -->
        @if($alertExceptions->count() > 0)
            <div class="bg-gradient-to-r from-amber-500/10 via-rose-500/10 to-amber-500/10 border-2 border-amber-400/80 rounded-3xl p-6 shadow-sm space-y-4">
                <div class="flex items-center space-x-3.5">
                    <div class="w-11 h-11 rounded-2xl bg-amber-500 text-white flex items-center justify-center flex-shrink-0 shadow-md">
                        <svg class="w-6 h-6 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-amber-950">Action Required: Customs Clearance & Risk Alerts</h3>
                        <p class="text-xs text-amber-800 mt-0.5">We have detected operational holds or documentation inspections affecting {{ $alertExceptions->count() }} of your consignments.</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-3 pt-1">
                    @foreach($alertExceptions as $exc)
                        <div class="p-4 bg-white/95 rounded-2xl border border-amber-200 text-xs flex items-center justify-between shadow-2xs">
                            <div>
                                <div class="flex items-center space-x-2">
                                    <span class="font-mono font-bold text-slate-900">{{ $exc->shipment->shipment_number }}</span>
                                    <span class="px-2 py-0.5 rounded text-[10px] font-extrabold bg-rose-100 text-rose-800">{{ $exc->exception_type }}</span>
                                </div>
                                <div class="text-[11px] text-slate-600 mt-1">{{ Str::limit($exc->description, 70) }}</div>
                            </div>
                            <a href="{{ route('shipments.show', $exc->shipment) }}" class="px-3.5 py-2 rounded-xl bg-amber-600 hover:bg-amber-500 text-white font-bold text-xs transition-colors flex-shrink-0 ml-3 shadow-sm">
                                Review Incident &rarr;
                            </a>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Section Header & Interactive Filter Tabs -->
        <div class="space-y-4 border-b border-slate-300 pb-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h2 class="text-xl font-black text-slate-900 tracking-tight flex items-center space-x-2.5">
                        <span>My Active Consignments</span>
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-white text-ocean-700 border border-slate-300 shadow-2xs">
                            {{ $customerShipments->count() }} Registered
                        </span>
                    </h2>
                    <p class="text-xs text-slate-600 mt-1">Live voyage telemetry, visual milestone progress, and verified carrier positions</p>
                </div>

                <div class="flex items-center space-x-2 text-xs">
                    <a href="{{ route('shipments.index') }}" class="inline-flex items-center space-x-1.5 text-xs font-bold text-ocean-700 hover:text-ocean-800 transition-colors bg-white px-3.5 py-2 rounded-xl border border-slate-300 shadow-2xs hover:bg-slate-50">
                        <span>View Directory Table</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </a>
                </div>
            </div>

            <!-- 2. Interactive Filter Tabs (No Reload) -->
            @php
                $delayedCountCustomer = $customerShipments->filter(fn($s) => $s->is_delayed || $s->exceptions->count() > 0)->count();
            @endphp
            <div class="flex flex-wrap items-center gap-2 pt-1" id="customerShipmentTabs">
                <button type="button" data-filter="all"
                    class="client-tab-btn px-4 py-2 rounded-xl text-xs font-bold transition-all bg-slate-900 text-white shadow-sm flex items-center space-x-2">
                    <span>All Shipments</span>
                    <span class="px-2 py-0.5 rounded-full text-[10px] bg-slate-700 text-slate-100 font-bold">{{ $customerShipments->count() }}</span>
                </button>

                <button type="button" data-filter="in_transit"
                    class="client-tab-btn px-4 py-2 rounded-xl text-xs font-bold transition-all bg-white hover:bg-slate-100 text-slate-700 border border-slate-300 flex items-center space-x-2">
                    <span>In Transit</span>
                    <span class="px-2 py-0.5 rounded-full text-[10px] bg-sky-100 text-sky-800 font-bold">{{ $inTransitCount }}</span>
                </button>

                <button type="button" data-filter="delayed"
                    class="client-tab-btn px-4 py-2 rounded-xl text-xs font-bold transition-all bg-white hover:bg-slate-100 text-slate-700 border border-slate-300 flex items-center space-x-2">
                    <span>Delayed / Hold</span>
                    <span class="px-2 py-0.5 rounded-full text-[10px] bg-amber-100 text-amber-800 font-bold">{{ $delayedCountCustomer }}</span>
                </button>

                <button type="button" data-filter="delivered"
                    class="client-tab-btn px-4 py-2 rounded-xl text-xs font-bold transition-all bg-white hover:bg-slate-100 text-slate-700 border border-slate-300 flex items-center space-x-2">
                    <span>Delivered</span>
                    <span class="px-2 py-0.5 rounded-full text-[10px] bg-emerald-100 text-emerald-800 font-bold">{{ $deliveredCount }}</span>
                </button>
            </div>
        </div>

        <!-- Modern Cards List (Client Portal Cards) -->
        <div class="space-y-6" id="customerShipmentList">
            @forelse($customerShipments as $cShip)
                @php
                    $latestMilestone = $cShip->trackingEvents->first();
                    $allStatuses = ['CREATED', 'BOOKED', 'PICKED_UP', 'IN_TRANSIT', 'DELIVERED'];
                    $currentStatusIndex = array_search($cShip->current_status, $allStatuses);
                    if ($currentStatusIndex === false) $currentStatusIndex = 0;
                    $isDelayedOrHold = $cShip->is_delayed || $cShip->exceptions->count() > 0;
                @endphp

                <div class="client-shipment-card bg-white rounded-3xl border border-slate-300 shadow-md hover:shadow-lg transition-all overflow-hidden p-6 sm:p-7 space-y-6"
                     data-status="{{ $cShip->current_status }}"
                     data-delayed="{{ $isDelayedOrHold ? '1' : '0' }}">

                    <!-- Card Header Row -->
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-100 pb-4">
                        <div class="flex flex-wrap items-center gap-3">
                            <span class="font-mono text-sm font-black text-ocean-700 bg-ocean-50 px-3 py-1 rounded-xl border border-ocean-200">
                                {{ $cShip->shipment_number }}
                            </span>

                            <div class="flex items-center space-x-1.5 text-xs text-slate-600 font-semibold">
                                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                <span>{{ $cShip->carrier->name }} ({{ $cShip->carrier->type }})</span>
                            </div>

                            <span class="text-xs text-slate-300">&bull;</span>
                            <span class="text-xs text-slate-500 font-medium">{{ $cShip->cargo_type }}</span>
                        </div>

                        <!-- Status & Delay Badges -->
                        <div class="flex flex-wrap items-center gap-2">
                            @if($cShip->current_status === 'DELIVERED')
                                <span class="px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                    DELIVERED
                                </span>
                            @elseif($cShip->current_status === 'IN_TRANSIT')
                                <span class="px-3 py-1 rounded-full text-xs font-bold bg-sky-100 text-sky-800 border border-sky-200 flex items-center space-x-1.5">
                                    <span class="w-2 h-2 rounded-full bg-sky-500 animate-pulse"></span>
                                    <span>IN TRANSIT</span>
                                </span>
                            @else
                                <span class="px-3 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-800 border border-slate-300">
                                    {{ $cShip->current_status }}
                                </span>
                            @endif

                            @if($cShip->is_delayed)
                                <span class="px-3 py-1 rounded-xl text-xs font-bold {{ $cShip->delay_severity === 'HIGH' ? 'bg-rose-100 text-rose-800 border border-rose-200' : 'bg-amber-100 text-amber-800 border border-amber-200' }}">
                                    Delayed +{{ $cShip->delay_hours }}h ({{ $cShip->delay_severity }})
                                </span>
                            @endif
                        </div>
                    </div>

                    <!-- Voyage Origin & Destination Route -->
                    <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-center">
                        <!-- Origin -->
                        <div class="md:col-span-5 flex items-center space-x-3.5">
                            <div class="w-10 h-10 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-black text-xs flex-shrink-0 border border-emerald-200">
                                DEP
                            </div>
                            <div>
                                <div class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">Departure Terminal</div>
                                <div class="text-sm font-black text-slate-900 mt-0.5">{{ $cShip->originLocation->name }}</div>
                                <div class="text-xs text-slate-500 font-medium">{{ $cShip->originLocation->city }}, {{ $cShip->originLocation->country }} ({{ $cShip->originLocation->code }})</div>
                            </div>
                        </div>

                        <!-- Route Arrow -->
                        <div class="hidden md:flex md:col-span-2 items-center justify-center">
                            <div class="w-full flex items-center justify-center space-x-2 text-ocean-500">
                                <div class="h-0.5 flex-1 bg-slate-200"></div>
                                <svg class="w-5 h-5 flex-shrink-0 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                                <div class="h-0.5 flex-1 bg-slate-200"></div>
                            </div>
                        </div>

                        <!-- Destination -->
                        <div class="md:col-span-5 flex items-center space-x-3.5 md:justify-end text-left md:text-right">
                            <div class="order-2 md:order-1">
                                <div class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">Arrival Terminal</div>
                                <div class="text-sm font-black text-slate-900 mt-0.5">{{ $cShip->destinationLocation->name }}</div>
                                <div class="text-xs text-slate-500 font-medium">{{ $cShip->destinationLocation->city }}, {{ $cShip->destinationLocation->country }} ({{ $cShip->destinationLocation->code }})</div>
                            </div>
                            <div class="order-1 md:order-2 w-10 h-10 rounded-2xl bg-rose-50 text-rose-600 flex items-center justify-center font-black text-xs flex-shrink-0 border border-rose-200">
                                ARR
                            </div>
                        </div>
                    </div>

                    <!-- Visual Step Progress Bar -->
                    <div class="pt-2 pb-2">
                        <div class="relative flex items-center justify-between">
                            <!-- Background line -->
                            <div class="absolute left-4 right-4 top-4 h-1 bg-slate-200 -z-0"></div>
                            <!-- Active colored progress bar -->
                            <div class="absolute left-4 top-4 h-1 bg-ocean-500 -z-0 transition-all duration-700"
                                 style="width: calc({{ ($currentStatusIndex / 4) * 100 }}% - 32px)"></div>

                            @php
                                $stepsMap = [
                                    0 => ['code' => 'CREATED', 'label' => 'Booked'],
                                    1 => ['code' => 'BOOKED', 'label' => 'Confirmed'],
                                    2 => ['code' => 'PICKED_UP', 'label' => 'Departed'],
                                    3 => ['code' => 'IN_TRANSIT', 'label' => 'In Transit'],
                                    4 => ['code' => 'DELIVERED', 'label' => 'Delivered'],
                                ];
                            @endphp

                            @foreach($stepsMap as $sIdx => $step)
                                @php
                                    $isPassed = $sIdx < $currentStatusIndex;
                                    $isCurrent = $sIdx === $currentStatusIndex;
                                @endphp
                                <div class="flex flex-col items-center relative z-10">
                                    @if($isPassed)
                                        <div class="w-8 h-8 rounded-full bg-emerald-500 text-white flex items-center justify-center font-bold text-xs shadow-sm ring-4 ring-white">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                        </div>
                                    @elseif($isCurrent)
                                        <div class="relative flex items-center justify-center">
                                            <span class="absolute w-10 h-10 rounded-full bg-ocean-400 opacity-40 animate-ping"></span>
                                            <div class="w-8 h-8 rounded-full bg-ocean-600 text-white flex items-center justify-center font-bold text-xs shadow-md border-2 border-white ring-4 ring-ocean-100">
                                                <span class="w-2.5 h-2.5 rounded-full bg-white animate-pulse"></span>
                                            </div>
                                        </div>
                                    @else
                                        <div class="w-8 h-8 rounded-full bg-white text-slate-400 border-2 border-slate-200 flex items-center justify-center font-bold text-xs">
                                            {{ $sIdx + 1 }}
                                        </div>
                                    @endif
                                    <span class="text-[10px] mt-1.5 font-bold uppercase tracking-wider {{ $isCurrent ? 'text-ocean-600' : ($isPassed ? 'text-slate-800' : 'text-slate-400') }}">
                                        {{ $step['label'] }}
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Milestone Position & CTA Footer -->
                    <div class="pt-4 border-t border-slate-100 flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                        <div class="flex flex-wrap items-center gap-6 text-xs text-slate-500">
                            <!-- Current Milestone -->
                            <div class="flex items-center space-x-2">
                                <svg class="w-4 h-4 text-ocean-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                <div>
                                    <span class="font-bold text-slate-800">Current Position:</span>
                                    <span>{{ $latestMilestone ? $latestMilestone->location_name : $cShip->originLocation->name }}</span>
                                    <span class="text-slate-400 font-mono text-[11px] ml-1">
                                        ({{ $latestMilestone ? $latestMilestone->event_time->diffForHumans() : 'At departure port' }})
                                    </span>
                                </div>
                            </div>

                            <!-- Estimated Arrival -->
                            <div>
                                <span class="font-bold text-slate-800">Estimated Arrival:</span>
                                <span class="{{ $cShip->is_delayed ? 'text-amber-800 font-bold' : 'text-slate-700' }}">
                                    {{ $cShip->estimated_arrival ? $cShip->estimated_arrival->format('d M Y - H:i') . ' UTC' : $cShip->expected_arrival->format('d M Y - H:i') . ' UTC' }}
                                </span>
                            </div>
                        </div>

                        <!-- CTA Track on Map Button -->
                        <div class="flex items-center space-x-3 self-end lg:self-auto">
                            <a href="{{ route('shipments.show', $cShip) }}"
                               class="inline-flex items-center space-x-2 px-5 py-2.5 rounded-xl bg-slate-900 hover:bg-ocean-600 text-white text-xs font-bold shadow-md shadow-slate-900/10 transition-all group">
                                <span>View Live Map & Timeline</span>
                                <svg class="w-4 h-4 text-ocean-400 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                            </a>
                        </div>
                    </div>

                </div>
            @empty
                <div class="p-16 text-center text-slate-400 bg-white rounded-3xl border border-dashed border-slate-300 shadow-sm space-y-3">
                    <div class="w-16 h-16 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center mx-auto">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                    </div>
                    <div class="text-base font-bold text-slate-700">No Consignments Found</div>
                    <p class="text-xs text-slate-500 max-w-sm mx-auto">You do not have any registered shipments in the CargoTrace system yet.</p>
                </div>
            @endforelse

            <!-- Filter Empty Notice -->
            <div id="emptyTabNotice" class="hidden p-16 text-center text-slate-400 bg-white rounded-3xl border border-dashed border-slate-300 shadow-sm space-y-3">
                <div class="w-16 h-16 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center mx-auto">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                </div>
                <div class="text-base font-bold text-slate-700">No Consignments in this Filter</div>
                <p class="text-xs text-slate-500 max-w-sm mx-auto">There are no shipments matching the selected filter category.</p>
            </div>
        </div>

        <!-- 3. Section: Global Network & SLA Guarantees -->
        <section class="mt-14 pb-8">
            <div class="text-center max-w-2xl mx-auto mb-10 space-y-2">
                <div class="inline-flex items-center space-x-2 px-3 py-1 rounded-full bg-white text-ocean-700 border border-slate-300 text-[11px] font-extrabold uppercase tracking-wider shadow-2xs">
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                    <span>Enterprise SLA & Operational Reliability</span>
                </div>
                <h3 class="text-2xl font-black text-slate-900 tracking-tight">Global Network & Visibility Guarantees</h3>
                <p class="text-xs text-slate-600 leading-relaxed">
                    Engineered specifically for high-value maritime cargo moving across strategic Asia-Europe corridors with automated risk management.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Card 1: Real-Time Corridor Visibility -->
                <div class="bg-white rounded-3xl p-6 border border-slate-300 shadow-md hover:shadow-lg transition-all space-y-4">
                    <div class="w-12 h-12 rounded-2xl bg-sky-50 text-sky-600 flex items-center justify-center font-black border border-sky-200 shadow-sm">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <div>
                        <h4 class="text-base font-bold text-slate-900">Real-Time Corridor Visibility</h4>
                        <p class="text-xs text-slate-600 mt-2 leading-relaxed">
                            Continuous multi-modal waypoint tracking connecting Southeast Asian hubs (Jakarta, Singapore, Tanjung Pelepas) directly with major European discharge ports (Rotterdam, Hamburg, Antwerp).
                        </p>
                    </div>
                    <div class="pt-3 border-t border-slate-100 flex items-center space-x-2 text-[11px] font-mono text-ocean-700 font-bold">
                        <span class="w-1.5 h-1.5 rounded-full bg-ocean-500"></span>
                        <span>SEA &rarr; EUR Dedicated Route</span>
                    </div>
                </div>

                <!-- Card 2: Proactive Customs & Delay Alerts -->
                <div class="bg-white rounded-3xl p-6 border border-slate-300 shadow-md hover:shadow-lg transition-all space-y-4">
                    <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center font-black border border-amber-200 shadow-sm">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    </div>
                    <div>
                        <h4 class="text-base font-bold text-slate-900">Proactive Customs & Delay Alerts</h4>
                        <p class="text-xs text-slate-600 mt-2 leading-relaxed">
                            Automated evaluation engine detects schedule deviations exceeding {{ config('cargotrace.delay_threshold_hours', 12) }} hours, instantly flagging customs documentation holds and port congestion.
                        </p>
                    </div>
                    <div class="pt-3 border-t border-slate-100 flex items-center space-x-2 text-[11px] font-mono text-amber-700 font-bold">
                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                        <span>Threshold: &gt;{{ config('cargotrace.delay_threshold_hours', 12) }}h Automated Trigger</span>
                    </div>
                </div>

                <!-- Card 3: 24/7 Weather & Port Monitoring -->
                <div class="bg-white rounded-3xl p-6 border border-slate-300 shadow-md hover:shadow-lg transition-all space-y-4">
                    <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-black border border-emerald-200 shadow-sm">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 00-9.78 2.096A4.001 4.001 0 003 15z"/></svg>
                    </div>
                    <div>
                        <h4 class="text-base font-bold text-slate-900">24/7 Weather & Port Monitoring</h4>
                        <p class="text-xs text-slate-600 mt-2 leading-relaxed">
                            Live meteorological telemetric feeds powered by Open-Meteo and OpenStreetMap geocoding provide continuous environmental context at every waypoint and harbor.
                        </p>
                    </div>
                    <div class="pt-3 border-t border-slate-100 flex items-center space-x-2 text-[11px] font-mono text-emerald-700 font-bold">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                        <span>Open-Meteo & OpenStreetMap Synced</span>
                    </div>
                </div>
            </div>
        </section>

    </div>

@endif

@endsection

@push('scripts')
@if(auth()->user()->isAdmin() || auth()->user()->isOperator())
    <!-- Chart.js for Operations Dashboard -->
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
@else
    <!-- 3D Interactive Animated Globe (Globe.gl via CDN) -->
    <script src="//unpkg.com/globe.gl"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const container = document.getElementById('globeContainer');
            const fallback = document.getElementById('globeFallback');

            if (!container) return;

            // Strategic trade routes: Southeast Asia -> Europe Corridor
            const arcsData = [
                // Tanjung Priok (Jakarta) -> Rotterdam
                { startLat: -6.1054, startLng: 106.8837, endLat: 51.9044, endLng: 4.4298, name: 'Jakarta (IDJKT) → Rotterdam (NLRTM)' },
                // Singapore -> Hamburg
                { startLat: 1.2644, startLng: 103.8222, endLat: 53.5467, endLng: 9.9691, name: 'Singapore (SGSIN) → Hamburg (DEHAM)' },
                // Tanjung Pelepas -> Antwerp
                { startLat: 1.3622, startLng: 103.5511, endLat: 51.2194, endLng: 4.4025, name: 'Tanjung Pelepas (MYTPP) → Antwerp (BEANR)' },
                // Tanjung Priok (Jakarta) -> Antwerp
                { startLat: -6.1054, startLng: 106.8837, endLat: 51.2194, endLng: 4.4025, name: 'Jakarta (IDJKT) → Antwerp (BEANR)' },
                // Singapore -> Rotterdam
                { startLat: 1.2644, startLng: 103.8222, endLat: 51.9044, endLng: 4.4298, name: 'Singapore (SGSIN) → Rotterdam (NLRTM)' }
            ];

            // Ports Data
            const portsData = [
                { lat: -6.1054, lng: 106.8837, name: 'Port of Tanjung Priok (IDJKT)', country: 'Indonesia' },
                { lat: 1.2644, lng: 103.8222, name: 'Port of Singapore (SGSIN)', country: 'Singapore' },
                { lat: 1.3622, lng: 103.5511, name: 'Port of Tanjung Pelepas (MYTPP)', country: 'Malaysia' },
                { lat: 51.9044, lng: 4.4298, name: 'Port of Rotterdam (NLRTM)', country: 'Netherlands' },
                { lat: 53.5467, lng: 9.9691, name: 'Port of Hamburg (DEHAM)', country: 'Germany' },
                { lat: 51.2194, lng: 4.4025, name: 'Port of Antwerp (BEANR)', country: 'Belgium' }
            ];

            try {
                if (typeof Globe === 'undefined') {
                    if (fallback) fallback.classList.remove('hidden');
                    return;
                }

                const globe = Globe()
                    (container)
                    .globeImageUrl('//unpkg.com/three-globe/example/img/earth-night.jpg')
                    .bumpImageUrl('//unpkg.com/three-globe/example/img/earth-topology.png')
                    .showAtmosphere(true)
                    .atmosphereColor('#0ea5e9')
                    .atmosphereAltitude(0.18)
                    // Animated Curved Arcs
                    .arcsData(arcsData)
                    .arcColor(() => ['rgba(14, 165, 233, 0.9)', 'rgba(56, 189, 248, 0.95)'])
                    .arcDashLength(0.4)
                    .arcDashGap(0.2)
                    .arcDashAnimateTime(2500)
                    .arcStroke(1.8)
                    .arcAltitude(0.25)
                    // Ports Points
                    .pointsData(portsData)
                    .pointColor(() => '#38bdf8')
                    .pointAltitude(0.04)
                    .pointRadius(0.8)
                    .pointLabel(d => `
                        <div style="background: rgba(15,23,42,0.92); padding: 6px 10px; border-radius: 8px; border: 1px solid #0284c7; color: #fff; font-family: 'Plus Jakarta Sans', sans-serif; font-size: 11px; box-shadow: 0 4px 12px rgba(0,0,0,0.3);">
                            <b style="color: #38bdf8;">${d.name}</b><br>
                            <span style="color: #94a3b8; font-size: 10px;">${d.country} &bull; Corridor Hub</span>
                        </div>
                    `);

                // Initial Point of View centered along corridor (Middle East / Indian Ocean perspective)
                globe.pointOfView({ lat: 20, lng: 65, altitude: 2.2 }, 800);

                // Auto-rotation and controls
                globe.controls().autoRotate = true;
                globe.controls().autoRotateSpeed = 0.8;
                globe.controls().enableZoom = true;

                // Transparent scene background to blend with dark navy hero
                globe.backgroundColor('rgba(0,0,0,0)');

                // Responsive Resizing Observer
                const resizeObserver = new ResizeObserver(() => {
                    const width = container.clientWidth;
                    const height = container.clientHeight;
                    if (width > 0 && height > 0) {
                        globe.width(width);
                        globe.height(height);
                    }
                });
                resizeObserver.observe(container);

            } catch (err) {
                console.warn('Globe.gl initialization note:', err);
                if (fallback) fallback.classList.remove('hidden');
            }

            // Interactive Tab Filtering (Vanilla JS - No Reload)
            const tabButtons = document.querySelectorAll('.client-tab-btn');
            const shipmentCards = document.querySelectorAll('.client-shipment-card');
            const emptyTabNotice = document.getElementById('emptyTabNotice');

            tabButtons.forEach(btn => {
                btn.addEventListener('click', () => {
                    const filter = btn.getAttribute('data-filter');

                    tabButtons.forEach(b => {
                        b.classList.remove('bg-slate-900', 'text-white', 'shadow-sm');
                        b.classList.add('bg-white', 'text-slate-700', 'border', 'border-slate-300');
                        const bBadge = b.querySelector('span:last-child');
                        if (bBadge && b.getAttribute('data-filter') === 'all') {
                            bBadge.className = 'px-2 py-0.5 rounded-full text-[10px] bg-slate-200 text-slate-800 font-bold';
                        }
                    });

                    btn.classList.remove('bg-white', 'text-slate-700', 'border', 'border-slate-300');
                    btn.classList.add('bg-slate-900', 'text-white', 'shadow-sm');
                    const activeBadge = btn.querySelector('span:last-child');
                    if (activeBadge && filter === 'all') {
                        activeBadge.className = 'px-2 py-0.5 rounded-full text-[10px] bg-slate-700 text-slate-100 font-bold';
                    }

                    let visibleCount = 0;
                    shipmentCards.forEach(card => {
                        const status = card.getAttribute('data-status');
                        const isDelayed = card.getAttribute('data-delayed') === '1';

                        let show = false;
                        if (filter === 'all') {
                            show = true;
                        } else if (filter === 'in_transit') {
                            show = status === 'IN_TRANSIT';
                        } else if (filter === 'delayed') {
                            show = isDelayed;
                        } else if (filter === 'delivered') {
                            show = status === 'DELIVERED';
                        }

                        if (show) {
                            card.style.display = '';
                            visibleCount++;
                        } else {
                            card.style.display = 'none';
                        }
                    });

                    if (emptyTabNotice) {
                        emptyTabNotice.classList.toggle('hidden', visibleCount > 0);
                    }
                });
            });
        });
    </script>
@endif
@endpush
