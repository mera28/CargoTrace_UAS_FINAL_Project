@extends('layouts.app')

@section('title', 'Carriers Management')
@section('header-title', 'Global Carriers')
@section('header-subtitle', 'Manage multi-modal shipping lines, air couriers, and land fleet partners')

@section('content')
<div class="space-y-6">

    <!-- Top Action Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-lg font-bold text-slate-900 tracking-tight">Carrier Directory</h2>
            <p class="text-xs text-slate-500">Total {{ $carriers->count() }} freight carriers registered in the platform</p>
        </div>

        <button type="button" onclick="openAddModal()"
            class="inline-flex items-center space-x-2 px-4 py-2.5 bg-ocean-600 hover:bg-ocean-500 text-white rounded-xl text-xs font-semibold shadow-sm shadow-ocean-600/30 transition-all self-start sm:self-auto">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            <span>Register New Carrier</span>
        </button>
    </div>

    <!-- Carriers Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-500 font-semibold uppercase tracking-wider border-b border-slate-200/80">
                    <tr>
                        <th class="px-6 py-3.5">Carrier Name</th>
                        <th class="px-6 py-3.5">Carrier Code</th>
                        <th class="px-6 py-3.5">Transport Mode</th>
                        <th class="px-6 py-3.5">Contact Email</th>
                        <th class="px-6 py-3.5">Active Shipments</th>
                        <th class="px-6 py-3.5">Status</th>
                        <th class="px-6 py-3.5 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse($carriers as $carrier)
                        <tr class="hover:bg-slate-50/70 transition-colors">
                            <td class="px-6 py-4 font-bold text-slate-900 flex items-center space-x-3">
                                <div class="w-9 h-9 rounded-lg bg-navy-900 text-ocean-400 flex items-center justify-center font-bold text-xs flex-shrink-0 shadow-sm">
                                    {{ substr($carrier->code, 0, 2) }}
                                </div>
                                <div>
                                    <div class="text-sm text-slate-900 font-bold">{{ $carrier->name }}</div>
                                    <div class="text-[11px] text-slate-400 font-mono">ID: #{{ $carrier->id }}</div>
                                </div>
                            </td>
                            <td class="px-6 py-4 font-mono font-bold text-ocean-600">
                                <span class="px-2 py-1 bg-ocean-50 border border-ocean-200/60 rounded-md">
                                    {{ $carrier->code }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                @if($carrier->type === 'SEA')
                                    <span class="inline-flex items-center space-x-1 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-sky-100 text-sky-800">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"/></svg>
                                        <span>SEA FREIGHT</span>
                                    </span>
                                @elseif($carrier->type === 'AIR')
                                    <span class="inline-flex items-center space-x-1 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-indigo-100 text-indigo-800">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                                        <span>AIR CARGO</span>
                                    </span>
                                @else
                                    <span class="inline-flex items-center space-x-1 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-amber-100 text-amber-800">
                                        <span>LAND TRUCKING</span>
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-slate-600">
                                {{ $carrier->contact_email ?? '-' }}
                            </td>
                            <td class="px-6 py-4">
                                <span class="font-semibold text-slate-800">{{ $carrier->shipments_count }}</span>
                                <span class="text-slate-400">shipments</span>
                            </td>
                            <td class="px-6 py-4">
                                @if($carrier->is_active)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-100 text-emerald-800">
                                        Active
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-slate-100 text-slate-600">
                                        Inactive
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right space-x-2">
                                <!-- Edit Button -->
                                <button type="button"
                                    onclick="openEditModal({{ json_encode($carrier) }})"
                                    class="inline-flex items-center px-2.5 py-1.5 rounded-lg border border-slate-200 text-slate-700 hover:bg-slate-100 hover:text-slate-900 transition-colors font-medium">
                                    <svg class="w-3.5 h-3.5 mr-1 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    <span>Edit</span>
                                </button>

                                <!-- Delete Form -->
                                <form action="{{ route('carriers.destroy', $carrier) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete carrier {{ $carrier->name }}? This action cannot be undone.');">
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
                                No carriers found. Click "Register New Carrier" to add one.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add Carrier Modal -->
<div id="addCarrierModal" class="fixed inset-0 z-50 hidden bg-slate-950/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-slate-200 animate-in fade-in zoom-in-95 duration-150">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-4">
            <div>
                <h3 class="text-base font-bold text-slate-900">Register New Carrier</h3>
                <p class="text-xs text-slate-500">Add shipping line or freight forwarder to Master Data</p>
            </div>
            <button type="button" onclick="closeAddModal()" class="text-slate-400 hover:text-slate-600 p-1">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <form action="{{ route('carriers.store') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Carrier Name *</label>
                <input type="text" name="name" required placeholder="e.g. Evergreen Marine" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-xs focus:ring-2 focus:ring-ocean-500 focus:outline-none">
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Carrier Code (Unique) *</label>
                    <input type="text" name="code" required maxlength="10" placeholder="e.g. EGLV" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-xs font-mono uppercase focus:ring-2 focus:ring-ocean-500 focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Type *</label>
                    <select name="type" required class="w-full px-3 py-2 border border-slate-300 rounded-lg text-xs focus:ring-2 focus:ring-ocean-500 focus:outline-none">
                        <option value="SEA">SEA FREIGHT</option>
                        <option value="AIR">AIR CARGO</option>
                        <option value="LAND">LAND TRUCKING</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Contact Email</label>
                <input type="email" name="contact_email" placeholder="operations@carrier.com" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-xs focus:ring-2 focus:ring-ocean-500 focus:outline-none">
            </div>

            <div class="flex items-center space-x-2 pt-2">
                <input type="checkbox" name="is_active" id="add_is_active" value="1" checked class="w-4 h-4 text-ocean-600 rounded border-slate-300 focus:ring-ocean-500">
                <label for="add_is_active" class="text-xs text-slate-700 font-medium">Carrier is Active and available for bookings</label>
            </div>

            <div class="flex items-center justify-end space-x-3 pt-4 border-t border-slate-100">
                <button type="button" onclick="closeAddModal()" class="px-4 py-2 border border-slate-200 text-slate-700 rounded-lg text-xs font-semibold hover:bg-slate-50">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-ocean-600 hover:bg-ocean-500 text-white rounded-lg text-xs font-semibold shadow-sm">Save Carrier</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Carrier Modal -->
<div id="editCarrierModal" class="fixed inset-0 z-50 hidden bg-slate-950/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-slate-200 animate-in fade-in zoom-in-95 duration-150">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-4">
            <div>
                <h3 class="text-base font-bold text-slate-900">Edit Carrier</h3>
                <p class="text-xs text-slate-500">Update carrier profile and operational status</p>
            </div>
            <button type="button" onclick="closeEditModal()" class="text-slate-400 hover:text-slate-600 p-1">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <form id="editCarrierForm" method="POST" class="space-y-4">
            @csrf
            @method('PUT')

            <div>
                <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Carrier Name *</label>
                <input type="text" id="edit_name" name="name" required class="w-full px-3 py-2 border border-slate-300 rounded-lg text-xs focus:ring-2 focus:ring-ocean-500 focus:outline-none">
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Carrier Code *</label>
                    <input type="text" id="edit_code" name="code" required maxlength="10" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-xs font-mono uppercase focus:ring-2 focus:ring-ocean-500 focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Type *</label>
                    <select id="edit_type" name="type" required class="w-full px-3 py-2 border border-slate-300 rounded-lg text-xs focus:ring-2 focus:ring-ocean-500 focus:outline-none">
                        <option value="SEA">SEA FREIGHT</option>
                        <option value="AIR">AIR CARGO</option>
                        <option value="LAND">LAND TRUCKING</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Contact Email</label>
                <input type="email" id="edit_contact_email" name="contact_email" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-xs focus:ring-2 focus:ring-ocean-500 focus:outline-none">
            </div>

            <div class="flex items-center space-x-2 pt-2">
                <input type="checkbox" name="is_active" id="edit_is_active" value="1" class="w-4 h-4 text-ocean-600 rounded border-slate-300 focus:ring-ocean-500">
                <label for="edit_is_active" class="text-xs text-slate-700 font-medium">Carrier is Active and available for bookings</label>
            </div>

            <div class="flex items-center justify-end space-x-3 pt-4 border-t border-slate-100">
                <button type="button" onclick="closeEditModal()" class="px-4 py-2 border border-slate-200 text-slate-700 rounded-lg text-xs font-semibold hover:bg-slate-50">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-ocean-600 hover:bg-ocean-500 text-white rounded-lg text-xs font-semibold shadow-sm">Update Carrier</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openAddModal() {
        document.getElementById('addCarrierModal').classList.remove('hidden');
    }
    function closeAddModal() {
        document.getElementById('addCarrierModal').classList.add('hidden');
    }
    function openEditModal(carrier) {
        document.getElementById('editCarrierForm').action = '/carriers/' + carrier.id;
        document.getElementById('edit_name').value = carrier.name;
        document.getElementById('edit_code').value = carrier.code;
        document.getElementById('edit_type').value = carrier.type;
        document.getElementById('edit_contact_email').value = carrier.contact_email ?? '';
        document.getElementById('edit_is_active').checked = !!carrier.is_active;
        document.getElementById('editCarrierModal').classList.remove('hidden');
    }
    function closeEditModal() {
        document.getElementById('editCarrierModal').classList.add('hidden');
    }
</script>
@endsection
