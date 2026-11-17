<?php

namespace App\Http\Controllers;

use App\Models\Shipment;
use App\Services\TrackingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class TrackingEventController extends Controller
{
    public function __construct(protected TrackingService $trackingService)
    {
    }

    /**
     * Store a newly created tracking event for the shipment.
     */
    public function store(Request $request, Shipment $shipment): RedirectResponse
    {
        if (!auth()->user()->isAdmin() && !auth()->user()->isOperator()) {
            abort(403, 'Unauthorized action: Only Administrators and Operators can record tracking events.');
        }

        $validated = $request->validate([
            'status' => ['required', 'string'],
            'location_name' => ['required', 'string', 'max:255'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'description' => ['required', 'string'],
            'event_time' => ['required', 'date'],
        ]);

        $this->trackingService->recordTrackingEvent($shipment, $validated, $request->user());

        return redirect()->route('shipments.show', $shipment)
            ->with('success', "Milestone '{$validated['status']}' at {$validated['location_name']} recorded successfully.");
    }
}
