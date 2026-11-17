<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\Shipment;
use App\Models\ShipmentItem;
use App\Models\TrackingEvent;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class ShipmentService
{
    /**
     * Retrieve filtered shipments with eager loading and server-side pagination.
     */
    public function getFilteredShipments(User $user, array $filters = []): LengthAwarePaginator
    {
        $query = Shipment::with([
            'customer',
            'carrier',
            'originLocation',
            'destinationLocation',
            'items',
            'exceptions',
        ]);

        // Mandatory Role Rule: Customers are strictly restricted to their own shipments
        if ($user->isCustomer()) {
            $query->where('customer_id', $user->id);
        } elseif (!empty($filters['customer_id'])) {
            $query->where('customer_id', $filters['customer_id']);
        }

        // Search by shipment number or cargo type
        if (!empty($filters['search'])) {
            $search = trim($filters['search']);
            $query->where(function ($q) use ($search) {
                $q->where('shipment_number', 'like', "%{$search}%")
                  ->orWhere('cargo_type', 'like', "%{$search}%");
            });
        }

        // Filter by shipment status
        if (!empty($filters['status'])) {
            $query->where('current_status', $filters['status']);
        }

        // Filter by carrier
        if (!empty($filters['carrier_id'])) {
            $query->where('carrier_id', $filters['carrier_id']);
        }

        // Filter by origin location
        if (!empty($filters['origin_location_id'])) {
            $query->where('origin_location_id', $filters['origin_location_id']);
        }

        // Filter by destination location
        if (!empty($filters['destination_location_id'])) {
            $query->where('destination_location_id', $filters['destination_location_id']);
        }

        // Filter by delay status
        if (isset($filters['is_delayed']) && $filters['is_delayed'] !== '') {
            $isDelayed = filter_var($filters['is_delayed'], FILTER_VALIDATE_BOOLEAN);
            $query->where('is_delayed', $isDelayed);
        }

        // Filter by expected departure date range
        if (!empty($filters['date_from'])) {
            $query->whereDate('expected_departure', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->whereDate('expected_departure', '<=', $filters['date_to']);
        }

        return $query->latest()->paginate(10)->withQueryString();
    }

    /**
     * Create a new shipment with items, initial tracking event, customer notification, and audit log.
     */
    public function createShipment(array $data, array $items, User $actor): Shipment
    {
        return DB::transaction(function () use ($data, $items, $actor) {
            $shipmentNumber = $this->generateUniqueShipmentNumber();

            // Calculate total weight and volume from items
            $totalWeight = 0;
            $totalVolume = 0;
            foreach ($items as $item) {
                $totalWeight += (float) ($item['weight'] ?? 0);
                $totalVolume += (float) ($item['volume'] ?? 0);
            }

            // Fallback to explicit values if provided
            if ($totalWeight <= 0 && isset($data['total_weight'])) {
                $totalWeight = (float) $data['total_weight'];
            }
            if ($totalVolume <= 0 && isset($data['total_volume'])) {
                $totalVolume = (float) $data['total_volume'];
            }

            $shipment = Shipment::create([
                'shipment_number' => $shipmentNumber,
                'customer_id' => $data['customer_id'],
                'carrier_id' => $data['carrier_id'],
                'origin_location_id' => $data['origin_location_id'],
                'destination_location_id' => $data['destination_location_id'],
                'cargo_type' => $data['cargo_type'],
                'total_weight' => $totalWeight,
                'total_volume' => $totalVolume,
                'expected_departure' => $data['expected_departure'],
                'expected_arrival' => $data['expected_arrival'],
                'estimated_arrival' => $data['estimated_arrival'] ?? $data['expected_arrival'],
                'current_status' => 'CREATED',
                'is_delayed' => false,
                'delay_hours' => 0,
                'delay_severity' => null,
                'delay_reason' => null,
            ]);

            // Save items
            foreach ($items as $item) {
                ShipmentItem::create([
                    'shipment_id' => $shipment->id,
                    'item_name' => $item['item_name'],
                    'sku' => $item['sku'] ?? null,
                    'quantity' => (int) $item['quantity'],
                    'weight' => (float) $item['weight'],
                    'volume' => (float) $item['volume'],
                ]);
            }

            // Create initial tracking event
            $origin = $shipment->originLocation;
            TrackingEvent::create([
                'shipment_id' => $shipment->id,
                'status' => 'CREATED',
                'location_name' => $origin ? $origin->name : 'Origin Terminal',
                'latitude' => $origin ? $origin->latitude : null,
                'longitude' => $origin ? $origin->longitude : null,
                'description' => "Consignment registered and booking confirmed for reference {$shipment->shipment_number}.",
                'event_time' => now(),
                'recorded_by' => $actor->id,
            ]);

            // Send in-app notification to the customer
            Notification::create([
                'user_id' => $shipment->customer_id,
                'shipment_id' => $shipment->id,
                'title' => "Shipment {$shipment->shipment_number} Created",
                'message' => "Your new shipment {$shipment->shipment_number} ({$shipment->cargo_type}) has been created and registered.",
                'is_read' => false,
            ]);

            // Record in audit log
            AuditLogService::log('CREATED', 'Shipment', $shipment->id, null, $shipment->toArray());

            return $shipment;
        });
    }

    /**
     * Update basic shipment information and items.
     */
    public function updateShipment(Shipment $shipment, array $data, array $items, User $actor): Shipment
    {
        if ($shipment->current_status === 'DELIVERED') {
            throw new InvalidArgumentException("Cannot modify shipment {$shipment->shipment_number} because it has already been delivered.");
        }

        return DB::transaction(function () use ($shipment, $data, $items, $actor) {
            $oldValues = $shipment->only([
                'carrier_id',
                'customer_id',
                'origin_location_id',
                'destination_location_id',
                'cargo_type',
                'expected_departure',
                'expected_arrival',
                'estimated_arrival',
                'total_weight',
                'total_volume',
            ]);

            // Update items if provided
            if (!empty($items)) {
                $shipment->items()->delete();
                $totalWeight = 0;
                $totalVolume = 0;
                foreach ($items as $item) {
                    $w = (float) $item['weight'];
                    $v = (float) $item['volume'];
                    $totalWeight += $w;
                    $totalVolume += $v;

                    ShipmentItem::create([
                        'shipment_id' => $shipment->id,
                        'item_name' => $item['item_name'],
                        'sku' => $item['sku'] ?? null,
                        'quantity' => (int) $item['quantity'],
                        'weight' => $w,
                        'volume' => $v,
                    ]);
                }
                $data['total_weight'] = $totalWeight;
                $data['total_volume'] = $totalVolume;
            }

            $shipment->update($data);

            $newValues = $shipment->only([
                'carrier_id',
                'customer_id',
                'origin_location_id',
                'destination_location_id',
                'cargo_type',
                'expected_departure',
                'expected_arrival',
                'estimated_arrival',
                'total_weight',
                'total_volume',
            ]);

            AuditLogService::log('UPDATED', 'Shipment', $shipment->id, $oldValues, $newValues);

            return $shipment;
        });
    }

    /**
     * Delete a shipment only if its status is 'CREATED'.
     */
    public function deleteShipment(Shipment $shipment, User $actor): bool
    {
        if ($shipment->current_status !== 'CREATED') {
            throw new InvalidArgumentException("Cannot delete shipment {$shipment->shipment_number} because its status is '{$shipment->current_status}'. Only shipments with status 'CREATED' can be deleted.");
        }

        return DB::transaction(function () use ($shipment, $actor) {
            $oldValues = $shipment->toArray();
            $shipmentId = $shipment->id;

            AuditLogService::log('DELETED', 'Shipment', $shipmentId, $oldValues, null);

            return $shipment->delete();
        });
    }

    /**
     * Generate an incremental unique shipment number in format CT-YYYY-XXXX.
     */
    protected function generateUniqueShipmentNumber(): string
    {
        $year = date('Y');
        $latest = Shipment::where('shipment_number', 'like', "CT-{$year}-%")
            ->orderBy('id', 'desc')
            ->first();

        $sequence = 1;
        if ($latest && preg_match('/CT-\d{4}-(\d+)/', $latest->shipment_number, $matches)) {
            $sequence = (int) $matches[1] + 1;
        }

        do {
            $formatted = sprintf("CT-%s-%04d", $year, $sequence);
            $exists = Shipment::where('shipment_number', $formatted)->exists();
            if ($exists) {
                $sequence++;
            }
        } while ($exists);

        return $formatted;
    }
}
