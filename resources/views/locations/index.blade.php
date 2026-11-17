@extends('layouts.app')

@section('title', 'Port & Hub Locations')
@section('header-title', 'Maritime Ports & Hubs')
@section('header-subtitle', 'Manage strategic seaport and airport terminals across Southeast Asia & Europe')

@section('content')
<div class="space-y-6">

    <!-- Top Action Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-lg font-bold text-slate-900 tracking-tight">Location Terminals Directory</h2>
            <p class="text-xs text-slate-500">Total {{ $locations->count() }} strategic global ports configured</p>
        </div>

        <button type="button" onclick="openAddModal()"
            class="inline-flex items-center space-x-2 px-4 py-2.5 bg-ocean-600 hover:bg-ocean-500 text-white rounded-xl text-xs font-semibold shadow-sm shadow-ocean-600/30 transition-all self-start sm:self-auto">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            <span>Register New Location</span>
        </button>
    </div>

    <!-- Locations Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-500 font-semibold uppercase tracking-wider border-b border-slate-200/80">
                    <tr>
                        <th class="px-6 py-3.5">Terminal / Port Name</th>
                        <th class="px-6 py-3.5">Port Code (UN/LOCODE)</th>
                        <th class="px-6 py-3.5">City & Country</th>
                        <th class="px-6 py-3.5">Corridor Region</th>
                        <th class="px-6 py-3.5">Geographic Coordinates</th>
                        <th class="px-6 py-3.5">Shipments Handled</th>
                        <th class="px-6 py-3.5 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse($locations as $loc)
                        <tr class="hover:bg-slate-50/70 transition-colors">
                            <td class="px-6 py-4 font-bold text-slate-900 flex items-center space-x-3">
                                <div class="w-9 h-9 rounded-lg bg-slate-100 text-ocean-600 border border-slate-200 flex items-center justify-center font-bold text-xs flex-shrink-0">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                </div>
                                <div>
                                    <div class="text-sm text-slate-900 font-bold">{{ $loc->name }}</div>
                                    <div class="text-[11px] text-slate-400 font-mono">Location ID: #{{ $loc->id }}</div>
                                </div>
                            </td>
                            <td class="px-6 py-4 font-mono font-bold text-ocean-600">
                                <span class="px-2 py-1 bg-ocean-50 border border-ocean-200/60 rounded-md">
                                    {{ $loc->code }}
                                </span>
                            </td>
                            <td class="px-6 py-4 font-medium text-slate-800">
                                {{ $loc->city }}, <span class="text-slate-500">{{ $loc->country }}</span>
                            </td>
                            <td class="px-6 py-4">
                                @if(str_contains(strtolower($loc->region), 'asia'))
                                    <span class="inline-flex items-center space-x-1 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-emerald-100 text-emerald-800">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                        <span>{{ $loc->region }}</span>
                                    </span>
                                @elseif(str_contains(strtolower($loc->region), 'europe'))
                                    <span class="inline-flex items-center space-x-1 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-sky-100 text-sky-800">
                                        <span class="w-1.5 h-1.5 rounded-full bg-sky-500"></span>
                                        <span>{{ $loc->region }}</span>
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-semibold bg-slate-100 text-slate-800">
                                        <span>{{ $loc->region }}</span>
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 font-mono text-[11px] text-slate-500">
                                {{ number_format($loc->latitude, 4) }}&deg;, {{ number_format($loc->longitude, 4) }}&deg;
                            </td>
                            <td class="px-6 py-4">
                                <span class="font-semibold text-slate-800">
                                    {{ $loc->origin_shipments_count + $loc->destination_shipments_count }}
                                </span>
                                <span class="text-slate-400 text-[11px]">
                                    ({{ $loc->origin_shipments_count }} dep / {{ $loc->destination_shipments_count }} arr)
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right space-x-2">
                                <!-- Edit Button -->
                                <button type="button"
                                    onclick="openEditModal({{ json_encode($loc) }})"
                                    class="inline-flex items-center px-2.5 py-1.5 rounded-lg border border-slate-200 text-slate-700 hover:bg-slate-100 hover:text-slate-900 transition-colors font-medium">
                                    <svg class="w-3.5 h-3.5 mr-1 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    <span>Edit</span>
                                </button>

                                <!-- Delete Form -->
                                <form action="{{ route('locations.destroy', $loc) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete location {{ $loc->name }}? This action cannot be undone.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                        class="inline-flex items-center px-2.5 py-1.5 rounded-lg border border-rose-200 text-rose-600 hover:bg-rose-50 transition-colors font-medium">
                                        <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        <span>Delete</span>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-8 text-center text-slate-400">
                                No locations found. Click "Register New Location" to add one.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add Location Modal -->
<div id="addLocationModal" class="fixed inset-0 z-50 hidden bg-slate-950/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-slate-200 animate-in fade-in zoom-in-95 duration-150">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-4">
            <div>
                <h3 class="text-base font-bold text-slate-900">Register New Terminal Location</h3>
                <p class="text-xs text-slate-500">Configure seaport or transit hub in Master Data</p>
            </div>
            <button type="button" onclick="closeAddModal()" class="text-slate-400 hover:text-slate-600 p-1">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <form action="{{ route('locations.store') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Terminal / Port Name *</label>
                <input type="text" name="name" required placeholder="e.g. Port of Felixstowe" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-xs focus:ring-2 focus:ring-ocean-500 focus:outline-none">
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Port Code (UN/LOCODE) *</label>
                    <input type="text" name="code" required maxlength="10" placeholder="e.g. GBFXT" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-xs font-mono uppercase focus:ring-2 focus:ring-ocean-500 focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Region *</label>
                    <select name="region" required class="w-full px-3 py-2 border border-slate-300 rounded-lg text-xs focus:ring-2 focus:ring-ocean-500 focus:outline-none">
                        <option value="Southeast Asia">Southeast Asia</option>
                        <option value="Europe">Europe</option>
                        <option value="East Asia">East Asia</option>
                        <option value="Middle East">Middle East</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">City *</label>
                    <input type="text" name="city" required placeholder="e.g. Felixstowe" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-xs focus:ring-2 focus:ring-ocean-500 focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Country *</label>
                    <input type="text" name="country" required placeholder="e.g. United Kingdom" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-xs focus:ring-2 focus:ring-ocean-500 focus:outline-none">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Latitude (-90 to 90) *</label>
                    <input type="number" step="0.0000001" name="latitude" required placeholder="e.g. 51.9567" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-xs font-mono focus:ring-2 focus:ring-ocean-500 focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Longitude (-180 to 180) *</label>
                    <input type="number" step="0.0000001" name="longitude" required placeholder="e.g. 1.3069" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-xs font-mono focus:ring-2 focus:ring-ocean-500 focus:outline-none">
                </div>
            </div>

            <div class="flex items-center justify-end space-x-3 pt-4 border-t border-slate-100">
                <button type="button" onclick="closeAddModal()" class="px-4 py-2 border border-slate-200 text-slate-700 rounded-lg text-xs font-semibold hover:bg-slate-50">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-ocean-600 hover:bg-ocean-500 text-white rounded-lg text-xs font-semibold shadow-sm">Save Location</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Location Modal -->
<div id="editLocationModal" class="fixed inset-0 z-50 hidden bg-slate-950/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-slate-200 animate-in fade-in zoom-in-95 duration-150">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-4">
            <div>
                <h3 class="text-base font-bold text-slate-900">Edit Terminal Location</h3>
                <p class="text-xs text-slate-500">Update port details and geographic coordinates</p>
            </div>
            <button type="button" onclick="closeEditModal()" class="text-slate-400 hover:text-slate-600 p-1">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <form id="editLocationForm" method="POST" class="space-y-4">
            @csrf
            @method('PUT')

            <div>
                <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Terminal / Port Name *</label>
                <input type="text" id="edit_name" name="name" required class="w-full px-3 py-2 border border-slate-300 rounded-lg text-xs focus:ring-2 focus:ring-ocean-500 focus:outline-none">
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Port Code *</label>
                    <input type="text" id="edit_code" name="code" required maxlength="10" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-xs font-mono uppercase focus:ring-2 focus:ring-ocean-500 focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Region *</label>
                    <select id="edit_region" name="region" required class="w-full px-3 py-2 border border-slate-300 rounded-lg text-xs focus:ring-2 focus:ring-ocean-500 focus:outline-none">
                        <option value="Southeast Asia">Southeast Asia</option>
                        <option value="Europe">Europe</option>
                        <option value="East Asia">East Asia</option>
                        <option value="Middle East">Middle East</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">City *</label>
                    <input type="text" id="edit_city" name="city" required class="w-full px-3 py-2 border border-slate-300 rounded-lg text-xs focus:ring-2 focus:ring-ocean-500 focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Country *</label>
                    <input type="text" id="edit_country" name="country" required class="w-full px-3 py-2 border border-slate-300 rounded-lg text-xs focus:ring-2 focus:ring-ocean-500 focus:outline-none">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Latitude *</label>
                    <input type="number" step="0.0000001" id="edit_latitude" name="latitude" required class="w-full px-3 py-2 border border-slate-300 rounded-lg text-xs font-mono focus:ring-2 focus:ring-ocean-500 focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Longitude *</label>
                    <input type="number" step="0.0000001" id="edit_longitude" name="longitude" required class="w-full px-3 py-2 border border-slate-300 rounded-lg text-xs font-mono focus:ring-2 focus:ring-ocean-500 focus:outline-none">
                </div>
            </div>

            <div class="flex items-center justify-end space-x-3 pt-4 border-t border-slate-100">
                <button type="button" onclick="closeEditModal()" class="px-4 py-2 border border-slate-200 text-slate-700 rounded-lg text-xs font-semibold hover:bg-slate-50">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-ocean-600 hover:bg-ocean-500 text-white rounded-lg text-xs font-semibold shadow-sm">Update Location</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openAddModal() {
        document.getElementById('addLocationModal').classList.remove('hidden');
    }
    function closeAddModal() {
        document.getElementById('addLocationModal').classList.add('hidden');
    }
    function openEditModal(loc) {
        document.getElementById('editLocationForm').action = '/locations/' + loc.id;
        document.getElementById('edit_name').value = loc.name;
        document.getElementById('edit_code').value = loc.code;
        document.getElementById('edit_region').value = loc.region;
        document.getElementById('edit_city').value = loc.city;
        document.getElementById('edit_country').value = loc.country;
        document.getElementById('edit_latitude').value = loc.latitude;
        document.getElementById('edit_longitude').value = loc.longitude;
        document.getElementById('editLocationModal').classList.remove('hidden');
    }
    function closeEditModal() {
        document.getElementById('editLocationModal').classList.add('hidden');
    }
</script>
@endsection
