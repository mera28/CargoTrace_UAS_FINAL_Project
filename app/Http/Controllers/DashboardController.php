<?php

namespace App\Http\Controllers;

use App\Models\Carrier;
use App\Models\Shipment;
use App\Models\ShipmentException;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Display the dynamic analytics dashboard tailored by user role.
     */
    public function index(Request $request): View
    {
        $user = $request->user();

        if ($user->isCustomer()) {
            return $this->customerDashboard($user);
        }

        return $this->operationsDashboard();
    }

    /**
     * Build dynamic analytics data for Administrator & Operator.
     */
    protected function operationsDashboard(): View
    {
        $totalShipments = Shipment::count();
        $inTransitCount = Shipment::where('current_status', 'IN_TRANSIT')->count();
        $deliveredCount = Shipment::where('current_status', 'DELIVERED')->count();
        $delayedCount = Shipment::where('is_delayed', true)->count();
        $openExceptionsCount = ShipmentException::whereIn('status', ['OPEN', 'INVESTIGATING'])->count();

        // Primary KPI: On-Time Delivery Rate (%)
        $onTimeCount = Shipment::where('is_delayed', false)->count();
        $onTimeRate = $totalShipments > 0 ? round(($onTimeCount / $totalShipments) * 100, 1) : 100.0;

        // Chart.js Aggregation 1: Shipments by Current Status
        $configuredStatuses = config('cargotrace.valid_statuses', ['CREATED', 'BOOKED', 'PICKED_UP', 'IN_TRANSIT', 'DELIVERED']);
        $rawStatusCounts = Shipment::select('current_status', DB::raw('count(*) as aggregate'))
            ->groupBy('current_status')
            ->pluck('aggregate', 'current_status')
            ->toArray();

        $statusLabels = $configuredStatuses;
        $statusData = [];
        foreach ($configuredStatuses as $st) {
            $statusData[] = (int) ($rawStatusCounts[$st] ?? 0);
        }

        // Chart.js Aggregation 2: Shipments by Carrier
        $carrierStats = Carrier::withCount('shipments')
            ->orderByDesc('shipments_count')
            ->get();
        $carrierLabels = $carrierStats->pluck('name')->toArray();
        $carrierData = $carrierStats->pluck('shipments_count')->toArray();

        // Priority Action Items: top 5 shipments that are delayed or have open/investigating exceptions
        $priorityShipments = Shipment::with(['customer', 'carrier', 'originLocation', 'destinationLocation', 'exceptions'])
            ->where(function ($q) {
                $q->where('is_delayed', true)
                  ->orWhereHas('exceptions', function ($sub) {
                      $sub->whereIn('status', ['OPEN', 'INVESTIGATING']);
                  });
            })
            ->orderByDesc('updated_at')
            ->take(5)
            ->get();

        return view('dashboard', compact(
            'totalShipments',
            'inTransitCount',
            'deliveredCount',
            'delayedCount',
            'openExceptionsCount',
            'onTimeRate',
            'statusLabels',
            'statusData',
            'carrierLabels',
            'carrierData',
            'priorityShipments'
        ));
    }

    /**
     * Build customer-specific portal data.
     */
    protected function customerDashboard(User $customer): View
    {
        $customerShipments = Shipment::with([
            'carrier',
            'originLocation',
            'destinationLocation',
            'trackingEvents' => fn ($q) => $q->orderByDesc('event_time'),
            'exceptions' => fn ($q) => $q->whereIn('status', ['OPEN', 'INVESTIGATING']),
        ])
        ->where('customer_id', $customer->id)
        ->orderByDesc('created_at')
        ->get();

        $totalShipments = $customerShipments->count();
        $inTransitCount = $customerShipments->where('current_status', 'IN_TRANSIT')->count();
        $deliveredCount = $customerShipments->where('current_status', 'DELIVERED')->count();

        // Active exceptions related to this customer
        $alertExceptions = ShipmentException::with('shipment')
            ->whereHas('shipment', fn ($q) => $q->where('customer_id', $customer->id))
            ->whereIn('status', ['OPEN', 'INVESTIGATING'])
            ->latest()
            ->get();

        $activeExceptionsCount = $alertExceptions->count();

        return view('dashboard', compact(
            'totalShipments',
            'inTransitCount',
            'deliveredCount',
            'activeExceptionsCount',
            'customerShipments',
            'alertExceptions'
        ));
    }
}
