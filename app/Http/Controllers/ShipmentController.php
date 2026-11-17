<?php

namespace App\Http\Controllers;

use App\Models\Carrier;
use App\Models\Location;
use App\Models\Shipment;
use App\Models\User;
use App\Services\ExternalLogisticsService;
use App\Services\ShipmentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use InvalidArgumentException;

class ShipmentController extends Controller
{
    protected ShipmentService $shipmentService;

    public function __construct(ShipmentService $shipmentService)
    {
        $this->shipmentService = $shipmentService;
    }

    /**
     * Display a listing of shipments (filtered per role and query).
     */
    public function index(Request $request): View
    {
        $shipments = $this->shipmentService->getFilteredShipments($request->user(), $request->all());
        $carriers = Carrier::where('is_active', true)->orderBy('name')->get();
        $locations = Location::orderBy('name')->get();

        return view('shipments.index', compact('shipments', 'carriers', 'locations'));
    }

    /**
     * Show the form for creating a new shipment (Admin/Operator only).
     */
    public function create(): View
    {
        $this->authorizeManagement();

        $carriers = Carrier::where('is_active', true)->orderBy('name')->get();
        $locations = Location::orderBy('name')->get();
        $customers = User::where('role', 'customer')->orderBy('name')->get();

        return view('shipments.create', compact('carriers', 'locations', 'customers'));
    }

    /**
     * Store a newly created shipment in storage (Admin/Operator only).
     */
    public function store(Request $request): RedirectResponse
    {
        $this->authorizeManagement();

        $validated = $request->validate([
            'customer_id' => ['required', 'exists:users,id'],
            'carrier_id' => ['required', 'exists:carriers,id'],
            'origin_location_id' => ['required', 'exists:locations,id'],
            'destination_location_id' => ['required', 'exists:locations,id', 'different:origin_location_id'],
            'cargo_type' => ['required', 'string', 'max:255'],
            'expected_departure' => ['required', 'date'],
            'expected_arrival' => ['required', 'date', 'after:expected_departure'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.item_name' => ['required', 'string', 'max:255'],
            'items.*.sku' => ['nullable', 'string', 'max:100'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.weight' => ['required', 'numeric', 'min:0.01'],
            'items.*.volume' => ['required', 'numeric', 'min:0.01'],
        ]);

        $shipment = $this->shipmentService->createShipment(
            $validated,
            $request->input('items', []),
            $request->user()
        );

        return redirect()->route('shipments.show', $shipment)
            ->with('success', "Shipment {$shipment->shipment_number} has been created and registered successfully.");
    }

    /**
     * Display the specified shipment.
     */
    public function show(Shipment $shipment, ExternalLogisticsService $logisticsService): View
    {
        // Role check: Customer can only view their own shipment
        if (auth()->user()->isCustomer() && $shipment->customer_id !== auth()->id()) {
            abort(403, 'Unauthorized: You are not authorized to view this consignment.');
        }

        $shipment->load([
            'customer',
            'carrier',
            'originLocation',
            'destinationLocation',
            'items',
            'trackingEvents.recorder',
            'exceptions.reporter',
            'notifications',
        ]);

        // Determine coordinates for operational weather context:
        // Prioritize latest recorded tracking event coordinates; fallback to destination location
        $latestEventWithCoords = $shipment->trackingEvents
            ->sortByDesc('event_time')
            ->first(fn ($event) => !is_null($event->latitude) && !is_null($event->longitude));

        if ($latestEventWithCoords) {
            $weatherLat = (float) $latestEventWithCoords->latitude;
            $weatherLon = (float) $latestEventWithCoords->longitude;
            $weatherLocationName = $latestEventWithCoords->location_name;
            $weatherContextType = 'Current Position';
        } else {
            $weatherLat = (float) $shipment->destinationLocation->latitude;
            $weatherLon = (float) $shipment->destinationLocation->longitude;
            $weatherLocationName = $shipment->destinationLocation->name;
            $weatherContextType = 'Destination Port';
        }

        $weather = $logisticsService->getLocationWeather($weatherLat, $weatherLon);
        $weather['location_name'] = $weatherLocationName;
        $weather['context_type'] = $weatherContextType;

        return view('shipments.show', compact('shipment', 'weather'));
    }

    /**
     * Show the form for editing the specified shipment (Admin/Operator only).
     */
    public function edit(Shipment $shipment): View|RedirectResponse
    {
        $this->authorizeManagement();

        if ($shipment->current_status === 'DELIVERED') {
            return redirect()->route('shipments.show', $shipment)
                ->with('error', "Delivered shipment {$shipment->shipment_number} cannot be modified.");
        }

        $shipment->load('items');
        $carriers = Carrier::where('is_active', true)->orderBy('name')->get();
        $locations = Location::orderBy('name')->get();
        $customers = User::where('role', 'customer')->orderBy('name')->get();

        return view('shipments.edit', compact('shipment', 'carriers', 'locations', 'customers'));
    }

    /**
     * Update the specified shipment in storage (Admin/Operator only).
     */
    public function update(Request $request, Shipment $shipment): RedirectResponse
    {
        $this->authorizeManagement();

        $validated = $request->validate([
            'customer_id' => ['required', 'exists:users,id'],
            'carrier_id' => ['required', 'exists:carriers,id'],
            'origin_location_id' => ['required', 'exists:locations,id'],
            'destination_location_id' => ['required', 'exists:locations,id', 'different:origin_location_id'],
            'cargo_type' => ['required', 'string', 'max:255'],
            'expected_departure' => ['required', 'date'],
            'expected_arrival' => ['required', 'date', 'after:expected_departure'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.item_name' => ['required', 'string', 'max:255'],
            'items.*.sku' => ['nullable', 'string', 'max:100'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.weight' => ['required', 'numeric', 'min:0.01'],
            'items.*.volume' => ['required', 'numeric', 'min:0.01'],
        ]);

        try {
            $this->shipmentService->updateShipment(
                $shipment,
                $validated,
                $request->input('items', []),
                $request->user()
            );

            return redirect()->route('shipments.show', $shipment)
                ->with('success', "Shipment {$shipment->shipment_number} has been updated successfully.");
        } catch (InvalidArgumentException $e) {
            return redirect()->route('shipments.show', $shipment)
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Remove the specified shipment from storage (Admin/Operator only).
     */
    public function destroy(Shipment $shipment): RedirectResponse
    {
        $this->authorizeManagement();

        try {
            $shipmentNumber = $shipment->shipment_number;
            $this->shipmentService->deleteShipment($shipment, auth()->user());

            return redirect()->route('shipments.index')
                ->with('success', "Shipment {$shipmentNumber} has been deleted successfully.");
        } catch (InvalidArgumentException $e) {
            return redirect()->route('shipments.index')
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Guard helper: only Admin and Operator can create/edit/delete shipments.
     */
    protected function authorizeManagement(): void
    {
        if (!auth()->user()->isAdmin() && !auth()->user()->isOperator()) {
            abort(403, 'Unauthorized action: Shipment management is restricted to Administrators and Operators.');
        }
    }
}
