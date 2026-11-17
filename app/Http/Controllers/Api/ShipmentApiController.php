<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Shipment;
use App\Services\ShipmentService;
use App\Services\TrackingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;

class ShipmentApiController extends Controller
{
    /**
     * List shipments accessible to the authenticated user.
     */
    public function index(Request $request, ShipmentService $service): JsonResponse
    {
        $shipments = $service->getFilteredShipments($request->user(), $request->all());

        return response()->json([
            'success' => true,
            'data' => $shipments,
        ], 200);
    }

    /**
     * Store a newly created shipment (Admin & Operator only).
     */
    public function store(Request $request, ShipmentService $service): JsonResponse
    {
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

        $shipment = $service->createShipment(
            $validated,
            $request->input('items', []),
            $request->user()
        );

        return response()->json([
            'success' => true,
            'message' => 'Shipment created successfully.',
            'data' => $shipment->load(['items', 'trackingEvents', 'carrier', 'originLocation', 'destinationLocation']),
        ], 201);
    }

    /**
     * Display the specified shipment.
     */
    public function show(Request $request, Shipment $shipment): JsonResponse
    {
        if ($request->user()->isCustomer() && $shipment->customer_id !== $request->user()->id) {
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
        ]);

        return response()->json([
            'success' => true,
            'data' => $shipment,
        ], 200);
    }

    /**
     * Update the specified shipment (Admin & Operator only).
     */
    public function update(Request $request, Shipment $shipment, ShipmentService $service): JsonResponse
    {
        $validated = $request->validate([
            'customer_id' => ['required', 'exists:users,id'],
            'carrier_id' => ['required', 'exists:carriers,id'],
            'origin_location_id' => ['required', 'exists:locations,id'],
            'destination_location_id' => ['required', 'exists:locations,id', 'different:origin_location_id'],
            'cargo_type' => ['required', 'string', 'max:255'],
            'expected_departure' => ['required', 'date'],
            'expected_arrival' => ['required', 'date', 'after:expected_departure'],
        ]);

        try {
            $updated = $service->updateShipment($shipment, $validated, $request->user());

            return response()->json([
                'success' => true,
                'message' => 'Shipment updated successfully.',
                'data' => $updated,
            ], 200);
        } catch (InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Delete the specified shipment (Admin & Operator only).
     */
    public function destroy(Request $request, Shipment $shipment, ShipmentService $service): JsonResponse
    {
        try {
            $service->deleteShipment($shipment, $request->user());

            return response()->json([
                'success' => true,
                'message' => "Shipment {$shipment->shipment_number} deleted successfully.",
            ], 200);
        } catch (InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Get tracking timeline events for the shipment.
     */
    public function tracking(Request $request, Shipment $shipment): JsonResponse
    {
        if ($request->user()->isCustomer() && $shipment->customer_id !== $request->user()->id) {
            abort(403, 'Unauthorized: You are not authorized to view tracking for this consignment.');
        }

        $events = $shipment->trackingEvents()
            ->with('recorder')
            ->orderBy('event_time')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $events,
        ], 200);
    }

    /**
     * Record a new tracking event milestone (Admin & Operator only).
     */
    public function recordTracking(Request $request, Shipment $shipment, TrackingService $trackingService): JsonResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'string'],
            'location_name' => ['required', 'string', 'max:255'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'event_time' => ['required', 'date'],
            'description' => ['required', 'string', 'max:1000'],
        ]);

        try {
            $event = $trackingService->recordTrackingEvent($shipment, $validated, $request->user());

            return response()->json([
                'success' => true,
                'message' => 'Tracking milestone recorded successfully.',
                'data' => $event,
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->validator->errors()->first() ?? 'Invalid status transition.',
                'errors' => $e->errors(),
            ], 422);
        } catch (InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}
