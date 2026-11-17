@extends('layouts.app')

@section('title', 'Dashboard')
@section('header-title', 'Logistics Overview')
@section('header-subtitle', 'Real-time Cargo Monitoring & Risk Intelligence')

@section('content')
<div class="space-y-6">

    <!-- Welcome Hero Card -->
    <div class="bg-gradient-to-r from-navy-900 via-navy-800 to-slate-900 rounded-2xl p-6 text-white shadow-lg border border-slate-800/80 relative overflow-hidden">
        <div class="absolute -right-10 -bottom-10 w-64 h-64 bg-ocean-600/10 rounded-full blur-3xl pointer-events-none"></div>
        
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 relative z-0">
            <div>
                <div class="flex items-center space-x-2 mb-2">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-ocean-500/20 text-ocean-300 border border-ocean-500/30">
                        {{ strtoupper(auth()->user()->role) }} ACCESS
                    </span>
                    <span class="text-xs text-slate-400">&bull;</span>
                    <span class="text-xs text-slate-300">{{ auth()->user()->company_name ?? 'Individual Shipper' }}</span>
                </div>
                <h2 class="text-2xl font-bold tracking-tight">
                    Welcome back, {{ auth()->user()->name }}!
                </h2>
                <p class="text-xs text-slate-300 mt-1 max-w-2xl">
                    @if(auth()->user()->isAdmin())
                        You have full administrative privileges to manage global carriers, port locations, shipments, and system audit logs.
                    @elseif(auth()->user()->isOperator())
                        You have operational privileges to record tracking events, manage shipment delays, and monitor maritime exceptions.
                    @else
                        You have customer access to track consignments, monitor estimated arrivals, and review export documentation.
                    @endif
                </p>
            </div>

            <!-- Quick Action or Status -->
            <div class="bg-slate-800/60 border border-slate-700/60 rounded-xl p-3 flex items-center space-x-3 text-xs self-start md:self-auto">
                <div class="w-10 h-10 rounded-lg bg-emerald-500/20 text-emerald-400 flex items-center justify-center font-bold">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div>
                    <div class="text-[11px] text-slate-400 font-semibold uppercase">System Status</div>
                    <div class="text-slate-100 font-bold">All Corridors Operational</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Active Scenario Parameters Cards (Requirement Group A) -->
    <div>
        <h3 class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-3">
            Active Project Parameters & Business Rules
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
            <!-- Parameter 1: Primary Corridor -->
            <div class="bg-white rounded-xl p-5 border border-slate-200 shadow-sm flex items-start space-x-4">
                <div class="w-12 h-12 rounded-xl bg-ocean-50 text-ocean-600 flex items-center justify-center flex-shrink-0">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Primary Corridor</div>
                    <div class="text-base font-bold text-slate-900 mt-0.5 truncate">
                        {{ config('cargotrace.primary_corridor', 'Southeast Asia -> Europe') }}
                    </div>
                    <p class="text-[11px] text-slate-500 mt-1">
                        Ports: IDJKT, SGSIN, MYTPP &rarr; NLRTM, DEHAM, BEANR
                    </p>
                </div>
            </div>

            <!-- Parameter 2: Delay Threshold -->
            <div class="bg-white rounded-xl p-5 border border-slate-200 shadow-sm flex items-start space-x-4">
                <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center flex-shrink-0">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Delay Threshold</div>
                    <div class="text-base font-bold text-slate-900 mt-0.5 flex items-baseline space-x-1.5">
                        <span>{{ config('cargotrace.delay_threshold_hours', 12) }} Hours</span>
                        <span class="text-xs font-normal text-amber-600 font-semibold">(Tolerance Limit)</span>
                    </div>
                    <p class="text-[11px] text-slate-500 mt-1">
                        Delays exceeding 12h trigger severity alerts & notifications
                    </p>
                </div>
            </div>

            <!-- Parameter 3: Primary KPI -->
            <div class="bg-white rounded-xl p-5 border border-slate-200 shadow-sm flex items-start space-x-4">
                <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center flex-shrink-0">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Primary KPI</div>
                    <div class="text-base font-bold text-slate-900 mt-0.5">
                        {{ config('cargotrace.primary_kpi', 'On-Time Delivery Rate') }}
                    </div>
                    <p class="text-[11px] text-slate-500 mt-1">
                        Measured against expected schedule vs actual/estimated
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Corridor Overview Table -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
            <div>
                <h3 class="text-sm font-bold text-slate-900">Current Corridor Shipments</h3>
                <p class="text-xs text-slate-500">Sample active consignments across Southeast Asia & Europe</p>
            </div>
            <span class="text-xs text-slate-400 font-medium">5 Seeded Records</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-500 font-semibold uppercase tracking-wider border-b border-slate-200/80">
                    <tr>
                        <th class="px-6 py-3">Shipment No.</th>
                        <th class="px-6 py-3">Customer</th>
                        <th class="px-6 py-3">Route (Origin &rarr; Dest)</th>
                        <th class="px-6 py-3">Carrier</th>
                        <th class="px-6 py-3">Status</th>
                        <th class="px-6 py-3">Delay Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    <tr class="hover:bg-slate-50/70 transition-colors">
                        <td class="px-6 py-3.5 font-bold font-mono text-ocean-600">CT-2026-001</td>
                        <td class="px-6 py-3.5 font-medium">PT Nusantara Export</td>
                        <td class="px-6 py-3.5">Port of Tanjung Priok (ID) &rarr; Rotterdam (NL)</td>
                        <td class="px-6 py-3.5 font-medium">Maersk Line</td>
                        <td class="px-6 py-3.5">
                            <span class="px-2 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-100 text-emerald-800">DELIVERED</span>
                        </td>
                        <td class="px-6 py-3.5 text-slate-500">
                            <span class="text-emerald-600 font-medium flex items-center space-x-1">
                                <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                <span>On Time (0h)</span>
                            </span>
                        </td>
                    </tr>
                    <tr class="hover:bg-slate-50/70 transition-colors bg-amber-50/20">
                        <td class="px-6 py-3.5 font-bold font-mono text-ocean-600">CT-2026-002</td>
                        <td class="px-6 py-3.5 font-medium">PT Nusantara Export</td>
                        <td class="px-6 py-3.5">Port of Singapore (SG) &rarr; Hamburg (DE)</td>
                        <td class="px-6 py-3.5 font-medium">MSC</td>
                        <td class="px-6 py-3.5">
                            <span class="px-2 py-0.5 rounded-full text-[11px] font-semibold bg-sky-100 text-sky-800">IN_TRANSIT</span>
                        </td>
                        <td class="px-6 py-3.5">
                            <span class="px-2 py-0.5 rounded text-[11px] font-bold bg-amber-100 text-amber-800">
                                Delayed 18h &bull; CUSTOMS_HOLD
                            </span>
                        </td>
                    </tr>
                    <tr class="hover:bg-slate-50/70 transition-colors bg-rose-50/20">
                        <td class="px-6 py-3.5 font-bold font-mono text-ocean-600">CT-2026-003</td>
                        <td class="px-6 py-3.5 font-medium">EuroTrade Imports BV</td>
                        <td class="px-6 py-3.5">Port of Tanjung Priok (ID) &rarr; Antwerp (BE)</td>
                        <td class="px-6 py-3.5 font-medium">CMA CGM</td>
                        <td class="px-6 py-3.5">
                            <span class="px-2 py-0.5 rounded-full text-[11px] font-semibold bg-sky-100 text-sky-800">IN_TRANSIT</span>
                        </td>
                        <td class="px-6 py-3.5">
                            <span class="px-2 py-0.5 rounded text-[11px] font-bold bg-rose-100 text-rose-800">
                                Delayed 30h &bull; HIGH SEVERITY
                            </span>
                        </td>
                    </tr>
                    <tr class="hover:bg-slate-50/70 transition-colors">
                        <td class="px-6 py-3.5 font-bold font-mono text-ocean-600">CT-2026-004</td>
                        <td class="px-6 py-3.5 font-medium">EuroTrade Imports BV</td>
                        <td class="px-6 py-3.5">Port of Tanjung Pelepas (MY) &rarr; Rotterdam (NL)</td>
                        <td class="px-6 py-3.5 font-medium">Maersk Line</td>
                        <td class="px-6 py-3.5">
                            <span class="px-2 py-0.5 rounded-full text-[11px] font-semibold bg-indigo-100 text-indigo-800">PICKED_UP</span>
                        </td>
                        <td class="px-6 py-3.5 text-slate-500">
                            <span class="text-emerald-600 font-medium flex items-center space-x-1">
                                <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                <span>On Time (Doc Resolved)</span>
                            </span>
                        </td>
                    </tr>
                    <tr class="hover:bg-slate-50/70 transition-colors">
                        <td class="px-6 py-3.5 font-bold font-mono text-ocean-600">CT-2026-005</td>
                        <td class="px-6 py-3.5 font-medium">PT Nusantara Export</td>
                        <td class="px-6 py-3.5">Port of Singapore (SG) &rarr; Rotterdam (NL)</td>
                        <td class="px-6 py-3.5 font-medium">DHL Global Forwarding</td>
                        <td class="px-6 py-3.5">
                            <span class="px-2 py-0.5 rounded-full text-[11px] font-semibold bg-slate-100 text-slate-800">CREATED</span>
                        </td>
                        <td class="px-6 py-3.5 text-slate-400">Scheduled Departure</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
