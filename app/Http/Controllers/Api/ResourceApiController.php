<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Carrier;
use App\Models\Location;
use App\Models\Shipment;
use App\Models\ShipmentException;
use App\Services\AuditLogService;
use App\Services\ExceptionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ResourceApiController extends Controller
{
    /**
     * List all carriers.
     */
    public function carriers(): JsonResponse
    {
        $carriers = Carrier::orderBy('name')->get();

        return response()->json([
            'success' => true,
            'data' => $carriers,
        ], 200);
    }

    /**
     * Store a new carrier (Admin only).
     */
    public function storeCarrier(Request $request): JsonResponse
    {
        if ($request->has('code')) {
            $request->merge(['code' => strtoupper(trim($request->code))]);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:10', 'unique:carriers,code'],
            'type' => ['required', 'string', 'in:SEA,AIR,LAND'],
            'contact_email' => ['nullable', 'email', 'max:255'],
            'is_active' => ['boolean'],
        ]);

        $validated['code'] = strtoupper($validated['code']);
        $carrier = Carrier::create($validated);

        AuditLogService::log('CREATED', 'Carrier', $carrier->id, null, $carrier->toArray());

        return response()->json([
            'success' => true,
            'message' => 'Carrier created successfully.',
            'data' => $carrier,
        ], 201);
    }

    /**
     * List all locations.
     */
    public function locations(): JsonResponse
    {
        $locations = Location::orderBy('name')->get();

        return response()->json([
            'success' => true,
            'data' => $locations,
        ], 200);
    }

    /**
     * Store a new location terminal (Admin only).
     */
    public function storeLocation(Request $request): JsonResponse
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

        return response()->json([
            'success' => true,
            'message' => 'Location terminal created successfully.',
            'data' => $location,
        ], 201);
    }

    /**
     * List exceptions for a given shipment.
     */
    public function shipmentExceptions(Request $request, Shipment $shipment): JsonResponse
    {
        if ($request->user()->isCustomer() && $shipment->customer_id !== $request->user()->id) {
            abort(403, 'Unauthorized: You are not authorized to view exceptions for this consignment.');
        }

        $exceptions = $shipment->exceptions()->with('reporter')->latest()->get();

        return response()->json([
            'success' => true,
            'data' => $exceptions,
        ], 200);
    }

    /**
     * Report a new operational incident / exception (Admin & Operator only).
     */
    public function storeException(Request $request, Shipment $shipment, ExceptionService $exceptionService): JsonResponse
    {
        $allowedTypes = config('cargotrace.exception_types', [
            'CUSTOMS_HOLD', 'WEATHER_DELAY', 'PORT_CONGESTION', 'DAMAGED', 'MISROUTED', 'DOCUMENTATION_ISSUE',
        ]);

        $validated = $request->validate([
            'exception_type' => ['required', 'string', 'in:' . implode(',', $allowedTypes)],
            'severity' => ['required', 'string', 'in:LOW,MEDIUM,HIGH'],
            'description' => ['required', 'string', 'max:1000'],
            'location_name' => ['nullable', 'string', 'max:255'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
        ]);

        $exception = $exceptionService->reportException($shipment, $validated, $request->user());

        return response()->json([
            'success' => true,
            'message' => 'Shipment exception reported successfully.',
            'data' => $exception,
        ], 201);
    }

    /**
     * Update exception status (Admin & Operator only).
     */
    public function updateExceptionStatus(Request $request, ShipmentException $exception, ExceptionService $exceptionService): JsonResponse
    {
        $allowedStatuses = config('cargotrace.exception_statuses', ['OPEN', 'INVESTIGATING', 'RESOLVED']);

        $validated = $request->validate([
            'status' => ['required', 'string', 'in:' . implode(',', $allowedStatuses)],
        ]);

        $updated = $exceptionService->updateExceptionStatus(
            $exception,
            $validated['status'],
            $request->user()
        );

        return response()->json([
            'success' => true,
            'message' => "Exception status updated to {$updated->status}.",
            'data' => $updated,
        ], 200);
    }
}
