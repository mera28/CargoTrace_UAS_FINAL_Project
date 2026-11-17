@extends('layouts.app')

@section('title', 'Create New Shipment')
@section('header-title', 'Register New Consignment')
@section('header-subtitle', 'Schedule a new freight movement across Southeast Asia & Europe corridor')

@section('content')
<div class="max-w-5xl mx-auto space-y-6">

    <!-- Back Navigation -->
    <div class="flex items-center justify-between">
        <a href="{{ route('shipments.index') }}" class="inline-flex items-center space-x-2 text-xs font-semibold text-slate-500 hover:text-slate-800 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            <span>Back to Shipments Directory</span>
        </a>
    </div>

    <!-- Main Creation Form -->
    <form action="{{ route('shipments.store') }}" method="POST" id="shipmentForm" class="space-y-6">
        @csrf

        <!-- Card 1: Consignment Header & Routing -->
        <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm space-y-5">
            <div class="border-b border-slate-100 pb-3">
                <h3 class="text-sm font-bold text-slate-900">1. Consignment Routing & Carrier Assignment</h3>
                <p class="text-xs text-slate-500">Define voyage parameters across the Southeast Asia &rarr; Europe corridor</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <!-- Customer Selection -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Customer Shipper *</label>
                    <select name="customer_id" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-lg text-xs text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-ocean-500">
                        <option value="">Select Customer Account</option>
                        @foreach($customers as $c)
                            <option value="{{ $c->id }}" {{ old('customer_id') == $c->id ? 'selected' : '' }}>
                                {{ $c->name }} ({{ $c->company_name ?? 'Individual' }}) - {{ $c->email }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Carrier Selection -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Freight Carrier *</label>
                    <select name="carrier_id" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-lg text-xs text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-ocean-500">
                        <option value="">Select Transport Carrier</option>
                        @foreach($carriers as $carrier)
                            <option value="{{ $carrier->id }}" {{ old('carrier_id') == $carrier->id ? 'selected' : '' }}>
                                {{ $carrier->name }} ({{ $carrier->code }}) - {{ $carrier->type }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Origin Port -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Origin Port / Terminal *</label>
                    <select name="origin_location_id" id="origin_location_id" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-lg text-xs text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-ocean-500">
                        <option value="">Select Origin Port (SEA)</option>
                        @foreach($locations as $loc)
                            <option value="{{ $loc->id }}" {{ old('origin_location_id') == $loc->id ? 'selected' : '' }}>
                                {{ $loc->code }} - {{ $loc->name }} ({{ $loc->city }}, {{ $loc->country }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Destination Port -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Destination Port / Terminal *</label>
                    <select name="destination_location_id" id="destination_location_id" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-lg text-xs text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-ocean-500">
                        <option value="">Select Destination Port (Europe)</option>
                        @foreach($locations as $loc)
                            <option value="{{ $loc->id }}" {{ old('destination_location_id') == $loc->id ? 'selected' : '' }}>
                                {{ $loc->code }} - {{ $loc->name }} ({{ $loc->city }}, {{ $loc->country }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Cargo Type -->
                <div class="md:col-span-2">
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Cargo Description / Commodity *</label>
                    <input type="text" name="cargo_type" value="{{ old('cargo_type') }}" required placeholder="e.g. Specialty Microelectronics & Industrial Robotics"
                        class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-lg text-xs text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-ocean-500">
                </div>

                <!-- Expected Departure -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Expected Departure (ETD) *</label>
                    <input type="datetime-local" name="expected_departure" value="{{ old('expected_departure') }}" required
                        class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-lg text-xs text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-ocean-500">
                </div>

                <!-- Expected Arrival -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Expected Arrival (ETA) *</label>
                    <input type="datetime-local" name="expected_arrival" value="{{ old('expected_arrival') }}" required
                        class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-lg text-xs text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-ocean-500">
                </div>
            </div>
        </div>

        <!-- Card 2: Dynamic Shipment Items -->
        <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div>
                    <h3 class="text-sm font-bold text-slate-900">2. Manifest & Cargo Items</h3>
                    <p class="text-xs text-slate-500">Add products, packages, quantities, and tare weights</p>
                </div>
                <button type="button" onclick="addItemRow()"
                    class="inline-flex items-center space-x-1.5 px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-xs font-semibold transition-colors">
                    <svg class="w-3.5 h-3.5 text-ocean-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    <span>+ Add Another Item</span>
                </button>
            </div>

            <!-- Items Table Container -->
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs" id="itemsTable">
                    <thead class="bg-slate-50 text-slate-500 uppercase font-semibold text-[11px] border-b border-slate-200">
                        <tr>
                            <th class="px-3 py-2.5">Item Description *</th>
                            <th class="px-3 py-2.5 w-36">SKU / Code</th>
                            <th class="px-3 py-2.5 w-24">Qty *</th>
                            <th class="px-3 py-2.5 w-32">Weight (kg) *</th>
                            <th class="px-3 py-2.5 w-32">Volume (cbm) *</th>
                            <th class="px-3 py-2.5 w-12 text-center"></th>
                        </tr>
                    </thead>
                    <tbody id="itemsBody" class="divide-y divide-slate-100">
                        <!-- Default Row 0 -->
                        <tr class="item-row">
                            <td class="px-3 py-2">
                                <input type="text" name="items[0][item_name]" required placeholder="e.g. Toraja Arabica Green Coffee"
                                    class="w-full px-2.5 py-1.5 bg-slate-50 border border-slate-300 rounded text-xs focus:bg-white focus:outline-none focus:ring-1 focus:ring-ocean-500">
                            </td>
                            <td class="px-3 py-2">
                                <input type="text" name="items[0][sku]" placeholder="COF-TRJ-01"
                                    class="w-full px-2.5 py-1.5 bg-slate-50 border border-slate-300 rounded text-xs font-mono focus:bg-white focus:outline-none focus:ring-1 focus:ring-ocean-500">
                            </td>
                            <td class="px-3 py-2">
                                <input type="number" name="items[0][quantity]" min="1" value="100" required
                                    class="w-full px-2.5 py-1.5 bg-slate-50 border border-slate-300 rounded text-xs focus:bg-white focus:outline-none focus:ring-1 focus:ring-ocean-500">
                            </td>
                            <td class="px-3 py-2">
                                <input type="number" step="0.01" min="0.01" name="items[0][weight]" value="5000" required
                                    class="w-full px-2.5 py-1.5 bg-slate-50 border border-slate-300 rounded text-xs focus:bg-white focus:outline-none focus:ring-1 focus:ring-ocean-500">
                            </td>
                            <td class="px-3 py-2">
                                <input type="number" step="0.01" min="0.01" name="items[0][volume]" value="15.5" required
                                    class="w-full px-2.5 py-1.5 bg-slate-50 border border-slate-300 rounded text-xs focus:bg-white focus:outline-none focus:ring-1 focus:ring-ocean-500">
                            </td>
                            <td class="px-3 py-2 text-center">
                                <button type="button" onclick="removeItemRow(this)" class="text-slate-400 hover:text-rose-600 p-1">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Form Action Bar -->
        <div class="flex items-center justify-end space-x-3">
            <a href="{{ route('shipments.index') }}" class="px-5 py-2.5 border border-slate-200 text-slate-700 rounded-xl text-xs font-semibold hover:bg-slate-50 transition-colors">
                Cancel
            </a>
            <button type="submit" class="px-6 py-2.5 bg-gradient-to-r from-ocean-600 to-sky-600 hover:from-ocean-500 hover:to-sky-500 text-white rounded-xl text-xs font-bold shadow-md shadow-ocean-600/30 transition-all flex items-center space-x-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                <span>Confirm & Create Shipment</span>
            </button>
        </div>
    </form>
</div>

<script>
    let itemIndex = 1;

    function addItemRow() {
        const tbody = document.getElementById('itemsBody');
        const row = document.createElement('tr');
        row.className = 'item-row';
        row.innerHTML = `
            <td class="px-3 py-2">
                <input type="text" name="items[${itemIndex}][item_name]" required placeholder="Item description..."
                    class="w-full px-2.5 py-1.5 bg-slate-50 border border-slate-300 rounded text-xs focus:bg-white focus:outline-none focus:ring-1 focus:ring-ocean-500">
            </td>
            <td class="px-3 py-2">
                <input type="text" name="items[${itemIndex}][sku]" placeholder="SKU code"
                    class="w-full px-2.5 py-1.5 bg-slate-50 border border-slate-300 rounded text-xs font-mono focus:bg-white focus:outline-none focus:ring-1 focus:ring-ocean-500">
            </td>
            <td class="px-3 py-2">
                <input type="number" name="items[${itemIndex}][quantity]" min="1" value="1" required
                    class="w-full px-2.5 py-1.5 bg-slate-50 border border-slate-300 rounded text-xs focus:bg-white focus:outline-none focus:ring-1 focus:ring-ocean-500">
            </td>
            <td class="px-3 py-2">
                <input type="number" step="0.01" min="0.01" name="items[${itemIndex}][weight]" value="100.00" required
                    class="w-full px-2.5 py-1.5 bg-slate-50 border border-slate-300 rounded text-xs focus:bg-white focus:outline-none focus:ring-1 focus:ring-ocean-500">
            </td>
            <td class="px-3 py-2">
                <input type="number" step="0.01" min="0.01" name="items[${itemIndex}][volume]" value="1.00" required
                    class="w-full px-2.5 py-1.5 bg-slate-50 border border-slate-300 rounded text-xs focus:bg-white focus:outline-none focus:ring-1 focus:ring-ocean-500">
            </td>
            <td class="px-3 py-2 text-center">
                <button type="button" onclick="removeItemRow(this)" class="text-slate-400 hover:text-rose-600 p-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                </button>
            </td>
        `;
        tbody.appendChild(row);
        itemIndex++;
    }

    function removeItemRow(btn) {
        const rows = document.querySelectorAll('.item-row');
        if (rows.length > 1) {
            btn.closest('tr').remove();
        } else {
            alert('A shipment must contain at least one cargo item.');
        }
    }
</script>
@endsection
