<?php

namespace Database\Seeders;

use App\Models\AuditLog;
use App\Models\Carrier;
use App\Models\Location;
use App\Models\Notification;
use App\Models\Shipment;
use App\Models\ShipmentException;
use App\Models\ShipmentItem;
use App\Models\TrackingEvent;
use App\Models\User;
use Illuminate\Database\Seeder;

class ShipmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $operator = User::where('email', 'operator@cargotrace.test')->first();
        $customer1 = User::where('email', 'customer1@cargotrace.test')->first();
        $customer2 = User::where('email', 'customer2@cargotrace.test')->first();

        $maersk = Carrier::where('code', 'MAEU')->first();
        $msc = Carrier::where('code', 'MSCU')->first();
        $cma = Carrier::where('code', 'CMDU')->first();
        $dhl = Carrier::where('code', 'DHLG')->first();

        $locJkt = Location::where('code', 'IDJKT')->first();
        $locSin = Location::where('code', 'SGSIN')->first();
        $locTpp = Location::where('code', 'MYTPP')->first();
        $locRtm = Location::where('code', 'NLRTM')->first();
        $locHam = Location::where('code', 'DEHAM')->first();
        $locAnr = Location::where('code', 'BEANR')->first();

        // -------------------------------------------------------------
        // Shipment 1: CT-2026-001 (IDJKT -> NLRTM, DELIVERED, on-time)
        // -------------------------------------------------------------
        $shipment1 = Shipment::updateOrCreate(
            ['shipment_number' => 'CT-2026-001'],
            [
                'customer_id' => $customer1->id,
                'carrier_id' => $maersk->id,
                'origin_location_id' => $locJkt->id,
                'destination_location_id' => $locRtm->id,
                'cargo_type' => 'Coffee & Spices',
                'total_weight' => 18500.00,
                'total_volume' => 65.50,
                'expected_departure' => '2026-10-01 08:00:00',
                'expected_arrival' => '2026-10-25 14:00:00',
                'estimated_arrival' => '2026-10-25 12:30:00',
                'current_status' => 'DELIVERED',
                'is_delayed' => false,
                'delay_hours' => 0,
                'delay_severity' => null,
                'delay_reason' => null,
            ]
        );

        ShipmentItem::create([
            'shipment_id' => $shipment1->id,
            'item_name' => 'Toraja Arabica Coffee Beans',
            'sku' => 'COF-TRJ-001',
            'quantity' => 250,
            'weight' => 12500.00,
            'volume' => 40.00,
        ]);
        ShipmentItem::create([
            'shipment_id' => $shipment1->id,
            'item_name' => 'Organic Cloves & Nutmeg',
            'sku' => 'SPC-MLK-002',
            'quantity' => 120,
            'weight' => 6000.00,
            'volume' => 25.50,
        ]);

        $trackingEvents1 = [
            [
                'status' => 'PICKED_UP',
                'location_name' => 'Port of Tanjung Priok',
                'latitude' => -6.1045,
                'longitude' => 106.8832,
                'description' => 'Cargo received and loaded onto vessel MAEU Pride at Tanjung Priok',
                'event_time' => '2026-10-01 08:00:00',
            ],
            [
                'status' => 'IN_TRANSIT',
                'location_name' => 'Strait of Malacca',
                'latitude' => 2.5000,
                'longitude' => 101.5000,
                'description' => 'Vessel cruising westbound through Malacca Strait safely',
                'event_time' => '2026-10-04 12:00:00',
            ],
            [
                'status' => 'IN_TRANSIT',
                'location_name' => 'Suez Canal Transit',
                'latitude' => 29.9753,
                'longitude' => 32.5599,
                'description' => 'Vessel convoy clearing Suez Canal northbound toward Mediterranean',
                'event_time' => '2026-10-15 09:30:00',
            ],
            [
                'status' => 'IN_TRANSIT',
                'location_name' => 'English Channel',
                'latitude' => 50.1000,
                'longitude' => -0.5000,
                'description' => 'Vessel entering North Sea approaches',
                'event_time' => '2026-10-22 16:00:00',
            ],
            [
                'status' => 'DELIVERED',
                'location_name' => 'Port of Rotterdam',
                'latitude' => 51.9490,
                'longitude' => 4.1453,
                'description' => 'Container safely discharged and received at destination terminal on schedule',
                'event_time' => '2026-10-25 14:00:00',
            ],
        ];

        foreach ($trackingEvents1 as $event) {
            TrackingEvent::create(array_merge($event, [
                'shipment_id' => $shipment1->id,
                'recorded_by' => $operator->id,
            ]));
        }

        Notification::create([
            'user_id' => $customer1->id,
            'shipment_id' => $shipment1->id,
            'title' => 'Shipment CT-2026-001 Delivered',
            'message' => 'Your shipment CT-2026-001 has been successfully delivered at Port of Rotterdam on time.',
            'is_read' => true,
        ]);

        // -------------------------------------------------------------
        // Shipment 2: CT-2026-002 (SGSIN -> DEHAM, IN_TRANSIT, delayed 18h, CUSTOMS_HOLD OPEN)
        // -------------------------------------------------------------
        $shipment2 = Shipment::updateOrCreate(
            ['shipment_number' => 'CT-2026-002'],
            [
                'customer_id' => $customer1->id,
                'carrier_id' => $msc->id,
                'origin_location_id' => $locSin->id,
                'destination_location_id' => $locHam->id,
                'cargo_type' => 'Electronic Components & Semi-conductors',
                'total_weight' => 14200.00,
                'total_volume' => 48.00,
                'expected_departure' => '2026-11-01 10:00:00',
                'expected_arrival' => '2026-11-20 18:00:00',
                'estimated_arrival' => '2026-11-21 12:00:00',
                'current_status' => 'IN_TRANSIT',
                'is_delayed' => true,
                'delay_hours' => 18,
                'delay_severity' => 'MEDIUM',
                'delay_reason' => 'Customs documentation inspection hold at transit transshipment hub',
            ]
        );

        ShipmentItem::create([
            'shipment_id' => $shipment2->id,
            'item_name' => 'Industrial Microcontroller Modules',
            'sku' => 'ELC-MCU-204',
            'quantity' => 500,
            'weight' => 8200.00,
            'volume' => 28.00,
        ]);
        ShipmentItem::create([
            'shipment_id' => $shipment2->id,
            'item_name' => 'Multilayer Ceramic Capacitors',
            'sku' => 'ELC-CAP-881',
            'quantity' => 1000,
            'weight' => 6000.00,
            'volume' => 20.00,
        ]);

        $trackingEvents2 = [
            [
                'status' => 'PICKED_UP',
                'location_name' => 'Port of Singapore',
                'latitude' => 1.2644,
                'longitude' => 103.8223,
                'description' => 'Container gated in and loaded at Pasir Panjang Terminal',
                'event_time' => '2026-11-01 10:00:00',
            ],
            [
                'status' => 'IN_TRANSIT',
                'location_name' => 'Indian Ocean',
                'latitude' => 6.0000,
                'longitude' => 80.0000,
                'description' => 'Vessel cruising toward Bab-el-Mandeb strait',
                'event_time' => '2026-11-07 14:00:00',
            ],
            [
                'status' => 'IN_TRANSIT',
                'location_name' => 'Red Sea - Port of Jeddah',
                'latitude' => 21.4858,
                'longitude' => 39.1925,
                'description' => 'Customs hold flag applied during intermediate security screening',
                'event_time' => '2026-11-12 14:00:00',
            ],
        ];

        foreach ($trackingEvents2 as $event) {
            TrackingEvent::create(array_merge($event, [
                'shipment_id' => $shipment2->id,
                'recorded_by' => $operator->id,
            ]));
        }

        $exc2 = ShipmentException::create([
            'shipment_id' => $shipment2->id,
            'exception_type' => 'CUSTOMS_HOLD',
            'severity' => 'MEDIUM',
            'status' => 'OPEN',
            'description' => 'Customs declaration discrepancy detected regarding tariff codes for imported integrated circuits.',
            'detected_at' => '2026-11-12 14:30:00',
            'resolved_at' => null,
            'reported_by' => $operator->id,
        ]);

        Notification::create([
            'user_id' => $customer1->id,
            'shipment_id' => $shipment2->id,
            'title' => 'Delay Alert: Shipment CT-2026-002',
            'message' => 'Shipment CT-2026-002 is experiencing an 18-hour delay due to CUSTOMS_HOLD at transit checkpoint.',
            'is_read' => false,
        ]);

        // -------------------------------------------------------------
        // Shipment 3: CT-2026-003 (IDJKT -> BEANR, IN_TRANSIT, delayed 30h, CUSTOMS_HOLD INVESTIGATING)
        // -------------------------------------------------------------
        $shipment3 = Shipment::updateOrCreate(
            ['shipment_number' => 'CT-2026-003'],
            [
                'customer_id' => $customer2->id,
                'carrier_id' => $cma->id,
                'origin_location_id' => $locJkt->id,
                'destination_location_id' => $locAnr->id,
                'cargo_type' => 'Furniture & Teak Wood Crafts',
                'total_weight' => 22400.00,
                'total_volume' => 72.00,
                'expected_departure' => '2026-11-02 06:00:00',
                'expected_arrival' => '2026-11-24 10:00:00',
                'estimated_arrival' => '2026-11-25 16:00:00',
                'current_status' => 'IN_TRANSIT',
                'is_delayed' => true,
                'delay_hours' => 30,
                'delay_severity' => 'HIGH',
                'delay_reason' => 'Phytosanitary inspection and physical container quarantine by customs authorities',
            ]
        );

        ShipmentItem::create([
            'shipment_id' => $shipment3->id,
            'item_name' => 'Handcrafted Solid Teak Dining Tables',
            'sku' => 'FRN-TEAK-01',
            'quantity' => 80,
            'weight' => 14400.00,
            'volume' => 46.00,
        ]);
        ShipmentItem::create([
            'shipment_id' => $shipment3->id,
            'item_name' => 'Rattan Weave Lounge Chairs',
            'sku' => 'FRN-RAT-09',
            'quantity' => 150,
            'weight' => 8000.00,
            'volume' => 26.00,
        ]);

        $trackingEvents3 = [
            [
                'status' => 'PICKED_UP',
                'location_name' => 'Port of Tanjung Priok',
                'latitude' => -6.1045,
                'longitude' => 106.8832,
                'description' => 'Vessel departure from Jakarta International Container Terminal',
                'event_time' => '2026-11-02 06:00:00',
            ],
            [
                'status' => 'IN_TRANSIT',
                'location_name' => 'Strait of Malacca',
                'latitude' => 3.1000,
                'longitude' => 100.5000,
                'description' => 'Passed north of Sumatra entering Andaman Sea',
                'event_time' => '2026-11-05 18:30:00',
            ],
            [
                'status' => 'IN_TRANSIT',
                'location_name' => 'Suez Canal Anchorage',
                'latitude' => 29.9500,
                'longitude' => 32.5500,
                'description' => 'Held in anchorage awaiting customs clearance documents',
                'event_time' => '2026-11-14 08:30:00',
            ],
        ];

        foreach ($trackingEvents3 as $event) {
            TrackingEvent::create(array_merge($event, [
                'shipment_id' => $shipment3->id,
                'recorded_by' => $operator->id,
            ]));
        }

        $exc3 = ShipmentException::create([
            'shipment_id' => $shipment3->id,
            'exception_type' => 'CUSTOMS_HOLD',
            'severity' => 'HIGH',
            'status' => 'INVESTIGATING',
            'description' => 'Timber legality verification (SVLK) documentation audit requested by customs quarantine officers.',
            'detected_at' => '2026-11-14 09:15:00',
            'resolved_at' => null,
            'reported_by' => $operator->id,
        ]);

        Notification::create([
            'user_id' => $customer2->id,
            'shipment_id' => $shipment3->id,
            'title' => 'High Severity Delay: CT-2026-003',
            'message' => 'Shipment CT-2026-003 delayed by 30 hours due to CUSTOMS_HOLD quarantine inspection.',
            'is_read' => false,
        ]);

        // -------------------------------------------------------------
        // Shipment 4: CT-2026-004 (MYTPP -> NLRTM, PICKED_UP, on-time, DOCUMENTATION_ISSUE RESOLVED)
        // -------------------------------------------------------------
        $shipment4 = Shipment::updateOrCreate(
            ['shipment_number' => 'CT-2026-004'],
            [
                'customer_id' => $customer2->id,
                'carrier_id' => $maersk->id,
                'origin_location_id' => $locTpp->id,
                'destination_location_id' => $locRtm->id,
                'cargo_type' => 'Palm Oil Derivatives & Specialty Chemicals',
                'total_weight' => 26000.00,
                'total_volume' => 55.00,
                'expected_departure' => '2026-11-16 11:00:00',
                'expected_arrival' => '2026-12-08 17:00:00',
                'estimated_arrival' => '2026-12-08 17:00:00',
                'current_status' => 'PICKED_UP',
                'is_delayed' => false,
                'delay_hours' => 0,
                'delay_severity' => null,
                'delay_reason' => null,
            ]
        );

        ShipmentItem::create([
            'shipment_id' => $shipment4->id,
            'item_name' => 'Refined Glycerine Drums',
            'sku' => 'CHM-GLY-501',
            'quantity' => 100,
            'weight' => 16000.00,
            'volume' => 32.00,
        ]);
        ShipmentItem::create([
            'shipment_id' => $shipment4->id,
            'item_name' => 'Stearic Acid Flakes in Bags',
            'sku' => 'CHM-STR-202',
            'quantity' => 400,
            'weight' => 10000.00,
            'volume' => 23.00,
        ]);

        $trackingEvents4 = [
            [
                'status' => 'BOOKED',
                'location_name' => 'Port of Tanjung Pelepas',
                'latitude' => 1.3622,
                'longitude' => 103.5489,
                'description' => 'Booking confirmed and export customs clearance documentation initiated',
                'event_time' => '2026-11-15 08:00:00',
            ],
            [
                'status' => 'PICKED_UP',
                'location_name' => 'Port of Tanjung Pelepas',
                'latitude' => 1.3622,
                'longitude' => 103.5489,
                'description' => 'Cargo safely loaded on board vessel Maersk Mc-Kinney Moller',
                'event_time' => '2026-11-16 11:00:00',
            ],
        ];

        foreach ($trackingEvents4 as $event) {
            TrackingEvent::create(array_merge($event, [
                'shipment_id' => $shipment4->id,
                'recorded_by' => $operator->id,
            ]));
        }

        $exc4 = ShipmentException::create([
            'shipment_id' => $shipment4->id,
            'exception_type' => 'DOCUMENTATION_ISSUE',
            'severity' => 'LOW',
            'status' => 'RESOLVED',
            'description' => 'Missing dangerous goods declaration certificate, revised document submitted and approved by carrier.',
            'detected_at' => '2026-11-15 10:00:00',
            'resolved_at' => '2026-11-16 09:00:00',
            'reported_by' => $operator->id,
        ]);

        Notification::create([
            'user_id' => $customer2->id,
            'shipment_id' => $shipment4->id,
            'title' => 'Exception Resolved: CT-2026-004',
            'message' => 'The documentation issue for shipment CT-2026-004 has been resolved. Vessel departed on schedule.',
            'is_read' => true,
        ]);

        // -------------------------------------------------------------
        // Shipment 5: CT-2026-005 (SGSIN -> NLRTM, CREATED, future schedule)
        // -------------------------------------------------------------
        $shipment5 = Shipment::updateOrCreate(
            ['shipment_number' => 'CT-2026-005'],
            [
                'customer_id' => $customer1->id,
                'carrier_id' => $dhl->id,
                'origin_location_id' => $locSin->id,
                'destination_location_id' => $locRtm->id,
                'cargo_type' => 'High-Value Aerospace Avionics',
                'total_weight' => 3500.00,
                'total_volume' => 18.00,
                'expected_departure' => '2026-11-20 14:00:00',
                'expected_arrival' => '2026-11-22 08:00:00',
                'estimated_arrival' => '2026-11-22 08:00:00',
                'current_status' => 'CREATED',
                'is_delayed' => false,
                'delay_hours' => 0,
                'delay_severity' => null,
                'delay_reason' => null,
            ]
        );

        ShipmentItem::create([
            'shipment_id' => $shipment5->id,
            'item_name' => 'Cockpit Navigation Flight Display Units',
            'sku' => 'AVN-DISP-77',
            'quantity' => 10,
            'weight' => 1500.00,
            'volume' => 8.00,
        ]);
        ShipmentItem::create([
            'shipment_id' => $shipment5->id,
            'item_name' => 'Satellite Communication Transceivers',
            'sku' => 'AVN-SAT-42',
            'quantity' => 25,
            'weight' => 2000.00,
            'volume' => 10.00,
        ]);

        TrackingEvent::create([
            'shipment_id' => $shipment5->id,
            'status' => 'CREATED',
            'location_name' => 'Changi Airfreight Centre',
            'latitude' => 1.3644,
            'longitude' => 103.9915,
            'description' => 'Consignment created and staged for customs pre-clearance and air waybill verification',
            'event_time' => '2026-11-17 10:00:00',
            'recorded_by' => $customer1->id,
        ]);

        Notification::create([
            'user_id' => $customer1->id,
            'shipment_id' => $shipment5->id,
            'title' => 'Shipment CT-2026-005 Created',
            'message' => 'Booking reference CT-2026-005 has been registered for departure on 2026-11-20.',
            'is_read' => true,
        ]);

        // -------------------------------------------------------------
        // Realistic Audit Logs
        // -------------------------------------------------------------
        AuditLog::create([
            'user_id' => $operator->id,
            'action' => 'CREATE_EXCEPTION',
            'entity_type' => 'ShipmentException',
            'entity_id' => $exc2->id,
            'old_values' => null,
            'new_values' => [
                'shipment_number' => 'CT-2026-002',
                'exception_type' => 'CUSTOMS_HOLD',
                'severity' => 'MEDIUM',
                'status' => 'OPEN',
            ],
            'ip_address' => '127.0.0.1',
        ]);

        AuditLog::create([
            'user_id' => $operator->id,
            'action' => 'UPDATE_DELAY_STATUS',
            'entity_type' => 'Shipment',
            'entity_id' => $shipment3->id,
            'old_values' => [
                'is_delayed' => false,
                'delay_hours' => 0,
            ],
            'new_values' => [
                'is_delayed' => true,
                'delay_hours' => 30,
                'delay_severity' => 'HIGH',
                'delay_reason' => 'Phytosanitary inspection and physical container quarantine by customs authorities',
            ],
            'ip_address' => '127.0.0.1',
        ]);

        AuditLog::create([
            'user_id' => $operator->id,
            'action' => 'RESOLVE_EXCEPTION',
            'entity_type' => 'ShipmentException',
            'entity_id' => $exc4->id,
            'old_values' => [
                'status' => 'OPEN',
            ],
            'new_values' => [
                'status' => 'RESOLVED',
                'resolved_at' => '2026-11-16 09:00:00',
            ],
            'ip_address' => '127.0.0.1',
        ]);

        AuditLog::create([
            'user_id' => $customer1->id,
            'action' => 'CREATE_SHIPMENT',
            'entity_type' => 'Shipment',
            'entity_id' => $shipment5->id,
            'old_values' => null,
            'new_values' => [
                'shipment_number' => 'CT-2026-005',
                'cargo_type' => 'High-Value Aerospace Avionics',
                'origin' => 'Port of Singapore',
                'destination' => 'Port of Rotterdam',
            ],
            'ip_address' => '127.0.0.1',
        ]);
    }
}
