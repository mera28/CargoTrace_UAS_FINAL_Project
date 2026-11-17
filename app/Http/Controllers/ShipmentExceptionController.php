<?php

namespace App\Http\Controllers;

use App\Models\Shipment;
use App\Models\ShipmentException;
use App\Services\EtaService;
use App\Services\ExceptionService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ShipmentExceptionController extends Controller
{
    public function __construct(
        protected EtaService $etaService,
        protected ExceptionService $exceptionService
    ) {
    }

    /**
     * Recalibrate estimated arrival and evaluate delay tolerance.
     */
    public function updateEta(Request $request, Shipment $shipment): RedirectResponse
    {
        $this->authorizeOperations();

        $validated = $request->validate([
            'estimated_arrival' => ['required', 'date'],
            'delay_reason' => ['nullable', 'string', 'max:500'],
        ]);

        $this->etaService->evaluateDelay(
            $shipment,
            Carbon::parse($validated['estimated_arrival']),
            $validated['delay_reason'] ?? null,
            $request->user()
        );

        return redirect()->route('shipments.show', $shipment)
            ->with('success', 'Estimated arrival and delay status successfully evaluated.');
    }

    /**
     * Log a new exception or customs hold on a shipment.
     */
    public function store(Request $request, Shipment $shipment): RedirectResponse
    {
        $this->authorizeOperations();

        $allowedTypes = config('cargotrace.exception_types', [
            'CUSTOMS_HOLD',
            'WEATHER_DELAY',
            'PORT_CONGESTION',
            'DAMAGED',
            'MISROUTED',
            'DOCUMENTATION_ISSUE',
        ]);

        $validated = $request->validate([
            'exception_type' => ['required', 'string', Rule::in($allowedTypes)],
            'severity' => ['required', 'string', Rule::in(['LOW', 'MEDIUM', 'HIGH'])],
            'description' => ['required', 'string', 'max:1000'],
            'location_name' => ['nullable', 'string', 'max:255'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
        ]);

        $this->exceptionService->reportException($shipment, $validated, $request->user());

        return redirect()->route('shipments.show', $shipment)
            ->with('success', "Exception incident '{$validated['exception_type']}' successfully reported and added to tracking timeline.");
    }

    /**
     * Update the resolution status of a reported exception.
     */
    public function updateStatus(Request $request, ShipmentException $exception): RedirectResponse
    {
        $this->authorizeOperations();

        $allowedStatuses = config('cargotrace.exception_statuses', ['OPEN', 'INVESTIGATING', 'RESOLVED']);

        $validated = $request->validate([
            'status' => ['required', 'string', Rule::in($allowedStatuses)],
        ]);

        $this->exceptionService->updateExceptionStatus($exception, $validated['status'], $request->user());

        return redirect()->route('shipments.show', $exception->shipment_id)
            ->with('success', "Exception '{$exception->exception_type}' status updated to {$validated['status']}.");
    }

    /**
     * Authorization guard for administrative & operational controls.
     */
    protected function authorizeOperations(): void
    {
        if (!auth()->user()->isAdmin() && !auth()->user()->isOperator()) {
            abort(403, 'Unauthorized action: Only Administrators and Operators can manage delays and exceptions.');
        }
    }
}
