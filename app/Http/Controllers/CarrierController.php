<?php

namespace App\Http\Controllers;

use App\Models\Carrier;
use App\Services\AuditLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CarrierController extends Controller
{
    /**
     * Display a listing of the carriers.
     */
    public function index(): View
    {
        $carriers = Carrier::withCount('shipments')->orderBy('name')->get();

        return view('carriers.index', compact('carriers'));
    }

    /**
     * Store a newly created carrier in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        if ($request->has('code')) {
            $request->merge(['code' => strtoupper(trim($request->code))]);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:10', 'unique:carriers,code'],
            'type' => ['required', 'string', 'in:SEA,AIR,LAND'],
            'contact_email' => ['nullable', 'email', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['code'] = strtoupper($validated['code']);
        $validated['is_active'] = $request->boolean('is_active', true);

        $carrier = Carrier::create($validated);

        AuditLogService::log('CREATED', 'Carrier', $carrier->id, null, $carrier->toArray());

        return redirect()->route('carriers.index')->with('success', "Carrier '{$carrier->name}' ({$carrier->code}) created successfully.");
    }

    /**
     * Update the specified carrier in storage.
     */
    public function update(Request $request, Carrier $carrier): RedirectResponse
    {
        if ($request->has('code')) {
            $request->merge(['code' => strtoupper(trim($request->code))]);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:10', 'unique:carriers,code,' . $carrier->id],
            'type' => ['required', 'string', 'in:SEA,AIR,LAND'],
            'contact_email' => ['nullable', 'email', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['code'] = strtoupper($validated['code']);
        $validated['is_active'] = $request->boolean('is_active');

        $oldValues = $carrier->only(['name', 'code', 'type', 'contact_email', 'is_active']);

        $carrier->update($validated);

        $newValues = $carrier->only(['name', 'code', 'type', 'contact_email', 'is_active']);

        AuditLogService::log('UPDATED', 'Carrier', $carrier->id, $oldValues, $newValues);

        return redirect()->route('carriers.index')->with('success', "Carrier '{$carrier->name}' updated successfully.");
    }

    /**
     * Remove the specified carrier from storage.
     */
    public function destroy(Carrier $carrier): RedirectResponse
    {
        if ($carrier->shipments()->exists()) {
            return redirect()->route('carriers.index')->with('error', "Cannot delete carrier '{$carrier->name}' ({$carrier->code}) because it has associated shipments.");
        }

        $oldValues = $carrier->toArray();
        $carrierId = $carrier->id;
        $carrierName = $carrier->name;

        $carrier->delete();

        AuditLogService::log('DELETED', 'Carrier', $carrierId, $oldValues, null);

        return redirect()->route('carriers.index')->with('success', "Carrier '{$carrierName}' has been deleted successfully.");
    }
}
