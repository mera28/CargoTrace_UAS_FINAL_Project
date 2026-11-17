<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\Shipment;
use App\Models\ShipmentException;
use App\Models\TrackingEvent;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class ExceptionService
{
    /**
     * Report a new operational or customs exception.
     */
    public function reportException(Shipment $shipment, array $data, User $actor): ShipmentException
    {
        return DB::transaction(function () use ($shipment, $data, $actor) {
            $exceptionType = $data['exception_type'] ?? 'CUSTOMS_HOLD';
            $severity = $data['severity'] ?? 'MEDIUM';
            $description = $data['description'];

            // 1. Save exception to shipment_exceptions
            $exception = ShipmentException::create([
                'shipment_id' => $shipment->id,
                'exception_type' => $exceptionType,
                'severity' => $severity,
                'status' => 'OPEN',
                'description' => $description,
                'detected_at' => now(),
                'reported_by' => $actor->id,
            ]);

            // 2. Add an automatic log event to tracking_events
            $latestEvent = $shipment->trackingEvents()->orderBy('event_time', 'desc')->first();
            $locationName = !empty($data['location_name'])
                ? $data['location_name']
                : ($latestEvent ? $latestEvent->location_name : $shipment->originLocation->name);

            $latitude = isset($data['latitude']) && $data['latitude'] !== ''
                ? (float) $data['latitude']
                : ($latestEvent?->latitude);

            $longitude = isset($data['longitude']) && $data['longitude'] !== ''
                ? (float) $data['longitude']
                : ($latestEvent?->longitude);

            TrackingEvent::create([
                'shipment_id' => $shipment->id,
                'status' => $shipment->current_status,
                'location_name' => $locationName,
                'latitude' => $latitude,
                'longitude' => $longitude,
                'description' => "[EXCEPTION - {$exceptionType}] {$description}",
                'event_time' => now(),
                'recorded_by' => $actor->id,
            ]);

            // 3. Customer in-app notification
            Notification::create([
                'user_id' => $shipment->customer_id,
                'shipment_id' => $shipment->id,
                'title' => "Incident Alert: {$shipment->shipment_number} ({$exceptionType})",
                'message' => "An operational exception ({$exceptionType} - {$severity}) was reported: {$description}",
                'is_read' => false,
            ]);

            // 4. Audit Log
            AuditLogService::log('EXCEPTION_REPORTED', 'ShipmentException', $exception->id, null, $exception->toArray());

            return $exception;
        });
    }

    /**
     * Update an exception's resolution status.
     */
    public function updateExceptionStatus(ShipmentException $exception, string $newStatus, User $actor): ShipmentException
    {
        $validStatuses = config('cargotrace.exception_statuses', ['OPEN', 'INVESTIGATING', 'RESOLVED']);

        if (!in_array($newStatus, $validStatuses, true)) {
            throw new InvalidArgumentException("Invalid exception status: '{$newStatus}'. Must be one of: " . implode(', ', $validStatuses));
        }

        return DB::transaction(function () use ($exception, $newStatus, $actor) {
            $oldValues = $exception->only(['status', 'resolved_at']);

            $exception->status = $newStatus;

            if ($newStatus === 'RESOLVED') {
                $exception->resolved_at = now();
            } else {
                $exception->resolved_at = null;
            }

            $exception->save();

            $newValues = $exception->only(['status', 'resolved_at']);

            // Notify customer of resolution or investigation update
            $shipment = $exception->shipment;
            Notification::create([
                'user_id' => $shipment->customer_id,
                'shipment_id' => $shipment->id,
                'title' => "Exception Update: {$exception->exception_type} ({$newStatus})",
                'message' => "Incident '{$exception->exception_type}' on shipment {$shipment->shipment_number} has been updated to status: {$newStatus}.",
                'is_read' => false,
            ]);

            // Audit Log
            AuditLogService::log('EXCEPTION_STATUS_UPDATED', 'ShipmentException', $exception->id, $oldValues, $newValues);

            return $exception;
        });
    }
}
