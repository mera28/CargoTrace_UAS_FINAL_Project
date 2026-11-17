<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\Shipment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class EtaService
{
    /**
     * Evaluate shipment delay against expected arrival and active threshold.
     */
    public function evaluateDelay(Shipment $shipment, Carbon $estimatedArrival, ?string $reason = null, User $actor): Shipment
    {
        return DB::transaction(function () use ($shipment, $estimatedArrival, $reason, $actor) {
            $expectedArrival = $shipment->expected_arrival;
            $threshold = (int) config('cargotrace.delay_threshold_hours', 12);

            // Calculate delay in hours
            if ($estimatedArrival->gt($expectedArrival)) {
                $delayHours = (int) $expectedArrival->diffInHours($estimatedArrival);
            } else {
                $delayHours = 0;
            }

            // Determine if delay reaches or exceeds threshold
            $isDelayed = false;
            $delaySeverity = null;

            if ($delayHours > 0 && $delayHours >= $threshold) {
                $isDelayed = true;

                if ($delayHours <= 6) {
                    $delaySeverity = 'LOW';
                } elseif ($delayHours <= 24) {
                    $delaySeverity = 'MEDIUM';
                } else {
                    $delaySeverity = 'HIGH';
                }
            }

            $oldValues = $shipment->only([
                'estimated_arrival',
                'is_delayed',
                'delay_hours',
                'delay_severity',
                'delay_reason',
            ]);

            $shipment->update([
                'estimated_arrival' => $estimatedArrival,
                'is_delayed' => $isDelayed,
                'delay_hours' => $delayHours,
                'delay_severity' => $delaySeverity,
                'delay_reason' => $reason,
            ]);

            $newValues = $shipment->only([
                'estimated_arrival',
                'is_delayed',
                'delay_hours',
                'delay_severity',
                'delay_reason',
            ]);

            // Notify customer if shipment is flagged as delayed
            if ($isDelayed) {
                Notification::create([
                    'user_id' => $shipment->customer_id,
                    'shipment_id' => $shipment->id,
                    'title' => "Delay Alert: {$shipment->shipment_number} (+{$delayHours}h)",
                    'message' => "Shipment {$shipment->shipment_number} has an updated estimated arrival ({$estimatedArrival->format('d M Y H:i')}) with +{$delayHours}h delay ({$delaySeverity} severity). Reason: " . ($reason ?? 'Schedule variance'),
                    'is_read' => false,
                ]);
            }

            // Record audit log
            AuditLogService::log('ETA_EVALUATED', 'Shipment', $shipment->id, $oldValues, $newValues);

            return $shipment;
        });
    }
}
