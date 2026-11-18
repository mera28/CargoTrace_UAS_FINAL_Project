@extends(auth()->user()->isCustomer() ? 'layouts.customer' : 'layouts.app')

@section('title', 'Shipments Directory')
@section('header-title', 'Shipment Management')
@section('header-subtitle', 'Real-time multi-modal consignments across the Southeast Asia & Europe corridor')

@section('content')
<div class="{{ auth()->user()->isCustomer() ? 'max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-6' : '' }} space-y-6">

    <!-- Top Action Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-lg font-bold text-slate-900 tracking-tight">Active Consignments</h2>
            <p class="text-xs text-slate-500">
                @if(auth()->user()->isCustomer())
                    Showing consignments booked by {{ auth()->user()->company_name ?? auth()->user()->name }}
                @else
                    Showing all global consignments across SEA &rarr; Europe corridor
                @endif
            </p>
        </div>

        @if(auth()->user()->isAdmin() || auth()->user()->isOperator())
            <a href="{{ route('shipments.create') }}"
                class="inline-flex items-center space-x-2 px-4 py-2.5 bg-ocean-600 hover:bg-ocean-500 text-white rounded-xl text-xs font-semibold shadow-sm shadow-ocean-600/30 transition-all self-start sm:self-auto">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                <span>+ Create Shipment</span>
            </a>
        @endif
    </div>

    <!-- Search & Filter Card -->
    <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
        <form method="GET" action="{{ route('shipments.index') }}" class="space-y-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-3">
                <!-- Search Keyword -->
                <div class="lg:col-span-2">
                    <label class="block text-[11px] font-semibold text-slate-500 uppercase tracking-wider mb-1">Search Keyword</label>
                    <div class="relative">
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Shipment No / Cargo type..."
                            class="w-full pl-8 pr-3 py-2 bg-slate-50 border border-slate-300 rounded-lg text-xs text-slate-800 placeholder-slate-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-ocean-500">
                        <svg class="w-4 h-4 text-slate-400 absolute left-2.5 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                </div>

                <!-- Status Filter -->
                <div>
                    <label class="block text-[11px] font-semibold text-slate-500 uppercase tracking-wider mb-1">Status</label>
                    <select name="status" class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-lg text-xs text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-ocean-500">
                        <option value="">All Statuses</option>
                        @foreach(['CREATED', 'BOOKED', 'PICKED_UP', 'IN_TRANSIT', 'DELIVERED'] as $st)
                            <option value="{{ $st }}" {{ request('status') === $st ? 'selected' : '' }}>{{ $st }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Carrier Filter -->
                <div>
                    <label class="block text-[11px] font-semibold text-slate-500 uppercase tracking-wider mb-1">Carrier</label>
                    <select name="carrier_id" class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-lg text-xs text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-ocean-500">
                        <option value="">All Carriers</option>
                        @foreach($carriers as $carrier)
                            <option value="{{ $carrier->id }}" {{ request('carrier_id') == $carrier->id ? 'selected' : '' }}>
                                {{ $carrier->name }} ({{ $carrier->code }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Origin Port -->
                <div>
                    <label class="block text-[11px] font-semibold text-slate-500 uppercase tracking-wider mb-1">Origin Port</label>
                    <select name="origin_location_id" class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-lg text-xs text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-ocean-500">
                        <option value="">All Origins</option>
                        @foreach($locations as $loc)
                            <option value="{{ $loc->id }}" {{ request('origin_location_id') == $loc->id ? 'selected' : '' }}>
                                {{ $loc->code }} - {{ $loc->city }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Delay Condition -->
                <div>
                    <label class="block text-[11px] font-semibold text-slate-500 uppercase tracking-wider mb-1">Delay Condition</label>
                    <select name="is_delayed" class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-lg text-xs text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-ocean-500">
                        <option value="">All</option>
                        <option value="0" {{ request('is_delayed') === '0' ? 'selected' : '' }}>On Schedule</option>
                        <option value="1" {{ request('is_delayed') === '1' ? 'selected' : '' }}>Delayed (&gt;12h)</option>
                    </select>
                </div>
            </div>

            <!-- Filter Buttons -->
            <div class="flex items-center justify-end space-x-2 pt-2 border-t border-slate-100">
                <a href="{{ route('shipments.index') }}" class="px-3 py-1.5 border border-slate-200 text-slate-600 rounded-lg text-xs font-semibold hover:bg-slate-50 transition-colors">
                    Reset Filter
                </a>
                <button type="submit" class="px-4 py-1.5 bg-slate-800 hover:bg-slate-900 text-white rounded-lg text-xs font-semibold shadow-sm transition-colors flex items-center space-x-1.5">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                    <span>Apply Filter</span>
                </button>
            </div>
        </form>
    </div>

    <!-- Shipments Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-500 font-semibold uppercase tracking-wider border-b border-slate-200/80">
                    <tr>
                        <th class="px-6 py-3.5">Shipment No.</th>
                        <th class="px-6 py-3.5">Customer / Cargo</th>
                        <th class="px-6 py-3.5">Corridor Route</th>
                        <th class="px-6 py-3.5">Carrier</th>
                        <th class="px-6 py-3.5">Status</th>
                        <th class="px-6 py-3.5">Expected Schedule</th>
                        <th class="px-6 py-3.5">Delay Status</th>
                        <th class="px-6 py-3.5 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse($shipments as $s)
                        <tr class="hover:bg-slate-50/70 transition-colors {{ $s->is_delayed ? 'bg-amber-50/20' : '' }}">
                            <td class="px-6 py-4 font-mono font-bold">
                                <a href="{{ route('shipments.show', $s) }}" class="text-ocean-600 hover:text-ocean-700 hover:underline flex items-center space-x-1.5">
                                    <span>{{ $s->shipment_number }}</span>
                                    <svg class="w-3.5 h-3.5 text-ocean-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                </a>
                                <span class="text-[10px] text-slate-400 font-sans font-normal">{{ $s->items->count() }} items &bull; {{ number_format($s->total_weight, 0) }} kg</span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="font-bold text-slate-900">{{ $s->customer->name }}</div>
                                <div class="text-[11px] text-slate-500 truncate max-w-xs">{{ $s->cargo_type }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center space-x-1 font-semibold text-slate-800">
                                    <span class="px-1.5 py-0.5 rounded bg-slate-100 border border-slate-200 text-slate-700 text-[11px]">
                                        {{ $s->originLocation->code }}
                                    </span>
                                    <span class="text-slate-400">&rarr;</span>
                                    <span class="px-1.5 py-0.5 rounded bg-slate-100 border border-slate-200 text-slate-700 text-[11px]">
                                        {{ $s->destinationLocation->code }}
                                    </span>
                                </div>
                                <div class="text-[10px] text-slate-400 mt-0.5">
                                    {{ $s->originLocation->city }} to {{ $s->destinationLocation->city }}
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="font-semibold text-slate-800">{{ $s->carrier->name }}</div>
                                <span class="text-[10px] text-slate-400 font-mono">{{ $s->carrier->type }}</span>
                            </td>
                            <td class="px-6 py-4">
                                @if($s->current_status === 'DELIVERED')
                                    <span class="px-2.5 py-1 rounded-full text-[11px] font-semibold bg-emerald-100 text-emerald-800">DELIVERED</span>
                                @elseif($s->current_status === 'IN_TRANSIT')
                                    <span class="px-2.5 py-1 rounded-full text-[11px] font-semibold bg-sky-100 text-sky-800">IN TRANSIT</span>
                                @elseif($s->current_status === 'PICKED_UP')
                                    <span class="px-2.5 py-1 rounded-full text-[11px] font-semibold bg-indigo-100 text-indigo-800">PICKED UP</span>
                                @elseif($s->current_status === 'BOOKED')
                                    <span class="px-2.5 py-1 rounded-full text-[11px] font-semibold bg-purple-100 text-purple-800">BOOKED</span>
                                @else
                                    <span class="px-2.5 py-1 rounded-full text-[11px] font-semibold bg-slate-100 text-slate-700">CREATED</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-[11px]">
                                <div><span class="text-slate-400">Dep:</span> {{ $s->expected_departure->format('d M Y') }}</div>
                                <div><span class="text-slate-400">Arr:</span> {{ $s->expected_arrival->format('d M Y') }}</div>
                            </td>
                            <td class="px-6 py-4">
                                @if($s->is_delayed)
                                    <div class="inline-flex items-center space-x-1 px-2 py-1 rounded text-[11px] font-bold {{ $s->delay_severity === 'HIGH' ? 'bg-rose-100 text-rose-800' : 'bg-amber-100 text-amber-800' }}">
                                        <svg class="w-3.5 h-3.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                                        <span>Delayed +{{ $s->delay_hours }}h</span>
                                    </div>
                                    @if($s->exceptions->count() > 0)
                                        <div class="text-[10px] text-amber-700 font-semibold mt-0.5">
                                            {{ $s->exceptions->first()->exception_type }} ({{ $s->exceptions->first()->status }})
                                        </div>
                                    @endif
                                @else
                                    <span class="inline-flex items-center space-x-1 text-emerald-700 font-semibold text-[11px]">
                                        <svg class="w-3.5 h-3.5 text-emerald-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                        <span>On Schedule</span>
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right space-x-2 whitespace-nowrap">
                                <a href="{{ route('shipments.show', $s) }}" class="inline-flex items-center px-2.5 py-1.5 rounded-lg border border-slate-200 text-slate-700 hover:bg-slate-100 transition-colors font-medium">
                                    <span>Details</span>
                                </a>

                                @if(auth()->user()->isAdmin() || auth()->user()->isOperator())
                                    @if($s->current_status !== 'DELIVERED')
                                        <a href="{{ route('shipments.edit', $s) }}" class="inline-flex items-center px-2.5 py-1.5 rounded-lg border border-slate-200 text-slate-700 hover:bg-slate-100 transition-colors font-medium">
                                            <span>Edit</span>
                                        </a>
                                    @endif

                                    @if($s->current_status === 'CREATED')
                                        <form action="{{ route('shipments.destroy', $s) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete shipment {{ $s->shipment_number }}?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="inline-flex items-center px-2.5 py-1.5 rounded-lg border border-rose-200 text-rose-600 hover:bg-rose-50 transition-colors font-medium">
                                                <span>Delete</span>
                                            </button>
                                        </form>
                                    @endif
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-8 text-center text-slate-400">
                                No shipments found matching the criteria.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($shipments->hasPages())
            <div class="px-6 py-4 border-t border-slate-100">
                {{ $shipments->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
