<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\Shipment;
use App\Models\TrackingEvent;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TrackingService
{
    /**
     * Define the strict finite state machine for shipment statuses.
     *
     * CREATED -> BOOKED -> PICKED_UP -> IN_TRANSIT (intermediate waypoints or advance) -> DELIVERED (terminal)
     */
    public function getAllowedNextStatuses(string $currentStatus): array
    {
        return match ($currentStatus) {
            'CREATED' => ['BOOKED'],
            'BOOKED' => ['PICKED_UP'],
            'PICKED_UP' => ['IN_TRANSIT'],
            'IN_TRANSIT' => ['IN_TRANSIT', 'DELIVERED'],
            'DELIVERED' => [],
            default => [],
        };
    }

    /**
     * Record a new tracking event, validate transition rules, chronologic integrity, and update shipment status.
     *
     * @throws ValidationException
     */
    public function recordTrackingEvent(Shipment $shipment, array $data, User $actor): TrackingEvent
    {
        $allowedStatuses = $this->getAllowedNextStatuses($shipment->current_status);

        // 1. Validate status transition
        if (!in_array($data['status'], $allowedStatuses, true)) {
            $allowedStr = empty($allowedStatuses) ? 'None (Shipment reached terminal DELIVERED status)' : implode(', ', $allowedStatuses);
            throw ValidationException::withMessages([
                'status' => "Invalid status transition: A shipment with status '{$shipment->current_status}' cannot transition to '{$data['status']}'. Allowed next status(es): {$allowedStr}.",
            ]);
        }

        // 2. Validate chronological integrity
        $latestEvent = $shipment->trackingEvents()->orderBy('event_time', 'desc')->first();
        $newEventTime = Carbon::parse($data['event_time']);

        if ($latestEvent && $newEventTime->lt(Carbon::parse($latestEvent->event_time))) {
            throw ValidationException::withMessages([
                'event_time' => "The event time ({$newEventTime->format('Y-m-d H:i')}) cannot be earlier than the most recent recorded event on {$latestEvent->event_time->format('Y-m-d H:i')}. Chronological tracking sequence must be preserved.",
            ]);
        }

        return DB::transaction(function () use ($shipment, $data, $actor, $newEventTime) {
            $oldStatus = $shipment->current_status;

            // 3. Save new tracking event (never overwrite historical events)
            $event = TrackingEvent::create([
                'shipment_id' => $shipment->id,
                'status' => $data['status'],
                'location_name' => $data['location_name'],
                'latitude' => isset($data['latitude']) && $data['latitude'] !== '' ? (float) $data['latitude'] : null,
                'longitude' => isset($data['longitude']) && $data['longitude'] !== '' ? (float) $data['longitude'] : null,
                'description' => $data['description'],
                'event_time' => $newEventTime,
                'recorded_by' => $actor->id,
            ]);

            // 4. Update current_status on shipment
            $shipment->update([
                'current_status' => $data['status'],
            ]);

            // 5. In-app notification for the customer
            Notification::create([
                'user_id' => $shipment->customer_id,
                'shipment_id' => $shipment->id,
                'title' => "Shipment {$shipment->shipment_number}: {$data['status']}",
                'message' => "Milestone update at {$data['location_name']}: {$data['description']}",
                'is_read' => false,
            ]);

            // 6. Record Audit Log
            AuditLogService::log(
                'TRACKING_EVENT_RECORDED',
                'TrackingEvent',
                $event->id,
                null,
                [
                    'shipment_number' => $shipment->shipment_number,
                    'status' => $data['status'],
                    'location' => $data['location_name'],
                    'event_time' => $newEventTime->toDateTimeString(),
                ]
            );

            if ($oldStatus !== $data['status']) {
                AuditLogService::log(
                    'STATUS_TRANSITION',
                    'Shipment',
                    $shipment->id,
                    ['current_status' => $oldStatus],
                    ['current_status' => $data['status']]
                );
            }

            return $event;
        });
    }
}
