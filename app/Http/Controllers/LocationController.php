<?php

namespace App\Http\Controllers;

use App\Models\Location;
use App\Services\AuditLogService;
use App\Services\ExternalLogisticsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LocationController extends Controller
{
    /**
     * Geocode location query via ExternalLogisticsService.
     */
    public function geocode(Request $request, ExternalLogisticsService $service): JsonResponse
    {
        $query = $request->query('query') ?? $request->query('q') ?? '';

        if (!is_string($query) || trim($query) === '') {
            return response()->json([
                'success' => false,
                'message' => 'Location query parameter is required.',
            ], 422);
        }

        $result = $service->geocodeLocation($query);

        if (!$result) {
            return response()->json([
                'success' => false,
                'message' => 'No coordinates found for the specified location.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $result,
        ]);
    }

    /**
     * Display a listing of the locations.
     */
    public function index(): View
    {
        $locations = Location::withCount(['originShipments', 'destinationShipments'])
            ->orderBy('country')
            ->orderBy('city')
            ->get();

        return view('locations.index', compact('locations'));
    }

    /**
     * Store a newly created location in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        if ($request->has('code')) {
            $request->merge(['code' => strtoupper(trim($request->code))]);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:10', 'unique:locations,code'],
            'city' => ['required', 'string', 'max:255'],
            'country' => ['required', 'string', 'max:255'],
            'region' => ['required', 'string', 'max:255'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
        ]);

        $validated['code'] = strtoupper($validated['code']);

        $location = Location::create($validated);

        AuditLogService::log('CREATED', 'Location', $location->id, null, $location->toArray());

        return redirect()->route('locations.index')->with('success', "Location '{$location->name}' ({$location->code}) created successfully.");
    }

    /**
     * Update the specified location in storage.
     */
    public function update(Request $request, Location $location): RedirectResponse
    {
        if ($request->has('code')) {
            $request->merge(['code' => strtoupper(trim($request->code))]);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:10', 'unique:locations,code,' . $location->id],
            'city' => ['required', 'string', 'max:255'],
            'country' => ['required', 'string', 'max:255'],
            'region' => ['required', 'string', 'max:255'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
        ]);

        $validated['code'] = strtoupper($validated['code']);

        $oldValues = $location->only(['name', 'code', 'city', 'country', 'region', 'latitude', 'longitude']);

        $location->update($validated);

        $newValues = $location->only(['name', 'code', 'city', 'country', 'region', 'latitude', 'longitude']);

        AuditLogService::log('UPDATED', 'Location', $location->id, $oldValues, $newValues);

        return redirect()->route('locations.index')->with('success', "Location '{$location->name}' updated successfully.");
    }

    /**
     * Remove the specified location from storage.
     */
    public function destroy(Location $location): RedirectResponse
    {
        if ($location->originShipments()->exists() || $location->destinationShipments()->exists()) {
            return redirect()->route('locations.index')->with('error', "Cannot delete location '{$location->name}' ({$location->code}) because it is actively used as origin or destination for shipments.");
        }

        $oldValues = $location->toArray();
        $locationId = $location->id;
        $locationName = $location->name;

        $location->delete();

        AuditLogService::log('DELETED', 'Location', $locationId, $oldValues, null);

        return redirect()->route('locations.index')->with('success', "Location '{$locationName}' deleted successfully.");
    }
}
