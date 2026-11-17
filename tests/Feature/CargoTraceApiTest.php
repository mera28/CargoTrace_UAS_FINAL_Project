<?php

namespace Tests\Feature;

use App\Models\Carrier;
use App\Models\Location;
use App\Models\Shipment;
use App\Models\User;
use App\Services\EtaService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CargoTraceApiTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $operator;
    protected User $customer1;
    protected User $customer2;
    protected Carrier $carrier;
    protected Location $origin;
    protected Location $destination;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Seed Users
        $this->admin = User::create([
            'name' => 'Admin Test',
            'email' => 'admin@cargotrace.test',
            'password' => Hash::make('password123'),
            'role' => 'admin',
            'company_name' => 'CargoTrace HQ',
        ]);

        $this->operator = User::create([
            'name' => 'Operator Test',
            'email' => 'operator@cargotrace.test',
            'password' => Hash::make('password123'),
            'role' => 'operator',
            'company_name' => 'CargoTrace Ops',
        ]);

        $this->customer1 = User::create([
            'name' => 'Siti Nusantara',
            'email' => 'customer1@cargotrace.test',
            'password' => Hash::make('password123'),
            'role' => 'customer',
            'company_name' => 'PT Nusantara Export',
        ]);

        $this->customer2 = User::create([
            'name' => 'Hendrik Euro',
            'email' => 'customer2@cargotrace.test',
            'password' => Hash::make('password123'),
            'role' => 'customer',
            'company_name' => 'EuroTrade BV',
        ]);

        // 2. Seed Carrier & Locations
        $this->carrier = Carrier::create([
            'name' => 'Maersk Line',
            'code' => 'MAEU',
            'type' => 'SEA',
            'contact_email' => 'ops@maersk.test',
            'is_active' => true,
        ]);

        $this->origin = Location::create([
            'name' => 'Port of Tanjung Priok',
            'code' => 'IDJKT',
            'city' => 'Jakarta',
            'country' => 'Indonesia',
            'region' => 'Southeast Asia',
            'latitude' => -6.1054,
            'longitude' => 106.8837,
        ]);

        $this->destination = Location::create([
            'name' => 'Port of Rotterdam',
            'code' => 'NLRTM',
            'city' => 'Rotterdam',
            'country' => 'Netherlands',
            'region' => 'Europe',
            'latitude' => 51.9044,
            'longitude' => 4.4298,
        ]);
    }

    /**
     * Scenario 1: API Authentication:
     * - Successful login returns 200 with Bearer token.
     * - Incorrect password returns 401.
     * - Access to protected route /api/shipments without token returns 401.
     */
    public function test_api_authentication_success_failure_and_unauthorized_access(): void
    {
        // 1. Success login
        $responseSuccess = $this->postJson('/api/login', [
            'email' => 'customer1@cargotrace.test',
            'password' => 'password123',
        ]);

        $responseSuccess->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Authenticated successfully.',
            ])
            ->assertJsonStructure([
                'data' => [
                    'user' => ['id', 'email', 'role'],
                    'token',
                    'token_type',
                ],
            ]);

        // 2. Invalid password returns 401
        $responseFailed = $this->postJson('/api/login', [
            'email' => 'customer1@cargotrace.test',
            'password' => 'wrongpassword',
        ]);

        $responseFailed->assertStatus(401)
            ->assertJson([
                'success' => false,
                'message' => 'Invalid email or password credentials.',
            ]);

        // 3. Unauthenticated access to /api/shipments returns 401
        $responseNoToken = $this->getJson('/api/shipments');
        $responseNoToken->assertStatus(401)
            ->assertJson([
                'success' => false,
                'message' => 'Unauthenticated.',
            ]);
    }

    /**
     * Scenario 2: Customer Authorization:
     * - Customer only sees own shipments on GET /api/shipments.
     * - Customer receives 403 when trying to access GET /api/shipments/{id} of another customer.
     */
    public function test_customer_authorization_scoping_and_cross_customer_forbidden(): void
    {
        // Create 1 shipment for customer1
        $shipment1 = Shipment::create([
            'shipment_number' => 'CT-2026-901',
            'customer_id' => $this->customer1->id,
            'carrier_id' => $this->carrier->id,
            'origin_location_id' => $this->origin->id,
            'destination_location_id' => $this->destination->id,
            'current_status' => 'CREATED',
            'cargo_type' => 'Garments',
            'total_weight' => 1500,
            'total_volume' => 30,
            'expected_departure' => Carbon::now()->addDays(2),
            'expected_arrival' => Carbon::now()->addDays(25),
        ]);

        // Create 1 shipment for customer2
        $shipment2 = Shipment::create([
            'shipment_number' => 'CT-2026-902',
            'customer_id' => $this->customer2->id,
            'carrier_id' => $this->carrier->id,
            'origin_location_id' => $this->origin->id,
            'destination_location_id' => $this->destination->id,
            'current_status' => 'CREATED',
            'cargo_type' => 'Machinery',
            'total_weight' => 3000,
            'total_volume' => 50,
            'expected_departure' => Carbon::now()->addDays(3),
            'expected_arrival' => Carbon::now()->addDays(28),
        ]);

        // Authenticate as Customer 1
        $token = $this->customer1->createToken('test_token')->plainTextToken;

        // 1. GET /api/shipments should only contain customer1's shipment
        $listResponse = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/shipments');

        $listResponse->assertStatus(200);
        $data = $listResponse->json('data.data');
        $shipmentNumbers = collect($data)->pluck('shipment_number');

        $this->assertTrue($shipmentNumbers->contains('CT-2026-901'));
        $this->assertFalse($shipmentNumbers->contains('CT-2026-902'));

        // 2. GET /api/shipments/{shipment2->id} belongs to customer2 -> expect 403 Forbidden
        $detailResponse = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/shipments/' . $shipment2->id);

        $detailResponse->assertStatus(403);
    }

    /**
     * Scenario 3: Role Action Authorization:
     * - Customer receives 403 when trying POST /api/shipments.
     * - Customer receives 403 when trying POST /api/shipments/{id}/tracking.
     */
    public function test_customer_receives_403_when_attempting_operator_actions(): void
    {
        $shipment = Shipment::create([
            'shipment_number' => 'CT-2026-903',
            'customer_id' => $this->customer1->id,
            'carrier_id' => $this->carrier->id,
            'origin_location_id' => $this->origin->id,
            'destination_location_id' => $this->destination->id,
            'current_status' => 'CREATED',
            'cargo_type' => 'Textiles',
            'total_weight' => 1200,
            'total_volume' => 20,
            'expected_departure' => Carbon::now()->addDays(2),
            'expected_arrival' => Carbon::now()->addDays(20),
        ]);

        $token = $this->customer1->createToken('test_token')->plainTextToken;

        // 1. Attempt POST /api/shipments as customer -> 403
        $createResponse = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/shipments', [
                'customer_id' => $this->customer1->id,
                'carrier_id' => $this->carrier->id,
                'origin_location_id' => $this->origin->id,
                'destination_location_id' => $this->destination->id,
                'cargo_type' => 'Coffee Beans',
                'expected_departure' => Carbon::now()->addDays(1)->toDateTimeString(),
                'expected_arrival' => Carbon::now()->addDays(22)->toDateTimeString(),
                'items' => [
                    ['item_name' => 'Arabica Coffee', 'quantity' => 100, 'weight' => 500, 'volume' => 10],
                ],
            ]);

        $createResponse->assertStatus(403);

        // 2. Attempt POST /api/shipments/{id}/tracking as customer -> 403
        $trackingResponse = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson("/api/shipments/{$shipment->id}/tracking", [
                'status' => 'BOOKED',
                'location_name' => 'Port of Tanjung Priok',
                'event_time' => Carbon::now()->toDateTimeString(),
                'description' => 'Unauthorized tracking update',
            ]);

        $trackingResponse->assertStatus(403);
    }

    /**
     * Scenario 4: Shipment Creation:
     * - Operator successfully creates a new shipment via POST /api/shipments (status 201)
     *   complete with manifest items and initial CREATED tracking milestone.
     */
    public function test_operator_creates_new_shipment_with_items_and_initial_milestone(): void
    {
        $token = $this->operator->createToken('operator_token')->plainTextToken;

        $payload = [
            'customer_id' => $this->customer1->id,
            'carrier_id' => $this->carrier->id,
            'origin_location_id' => $this->origin->id,
            'destination_location_id' => $this->destination->id,
            'cargo_type' => 'Organic Tea & Spices',
            'expected_departure' => Carbon::now()->addDays(2)->format('Y-m-d H:i:s'),
            'expected_arrival' => Carbon::now()->addDays(24)->format('Y-m-d H:i:s'),
            'items' => [
                [
                    'item_name' => 'Black Tea Grade A',
                    'sku' => 'TEA-BLK-01',
                    'quantity' => 200,
                    'weight' => 1200.50,
                    'volume' => 18.00,
                ],
                [
                    'item_name' => 'Nutmeg Whole',
                    'sku' => 'SPICE-NTM-02',
                    'quantity' => 150,
                    'weight' => 850.00,
                    'volume' => 12.00,
                ],
            ],
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/shipments', $payload);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Shipment created successfully.',
            ]);

        $createdShipmentNumber = $response->json('data.shipment_number');
        $this->assertNotEmpty($createdShipmentNumber);

        // Verify database persistence
        $this->assertDatabaseHas('shipments', [
            'shipment_number' => $createdShipmentNumber,
            'customer_id' => $this->customer1->id,
            'current_status' => 'CREATED',
            'cargo_type' => 'Organic Tea & Spices',
        ]);

        $this->assertDatabaseHas('shipment_items', [
            'item_name' => 'Black Tea Grade A',
            'sku' => 'TEA-BLK-01',
            'quantity' => 200,
        ]);

        $this->assertDatabaseHas('tracking_events', [
            'status' => 'CREATED',
            'location_name' => 'Port of Tanjung Priok',
        ]);
    }

    /**
     * Scenario 5: Invalid Status Transition:
     * - Operator is rejected (status 422) when attempting an invalid status jump
     *   (e.g., CREATED directly to DELIVERED).
     */
    public function test_invalid_status_transition_is_rejected_with_422(): void
    {
        $shipment = Shipment::create([
            'shipment_number' => 'CT-2026-905',
            'customer_id' => $this->customer1->id,
            'carrier_id' => $this->carrier->id,
            'origin_location_id' => $this->origin->id,
            'destination_location_id' => $this->destination->id,
            'current_status' => 'CREATED',
            'cargo_type' => 'Rubber Products',
            'total_weight' => 2000,
            'total_volume' => 25,
            'expected_departure' => Carbon::now()->addDays(1),
            'expected_arrival' => Carbon::now()->addDays(22),
        ]);

        $token = $this->operator->createToken('operator_token')->plainTextToken;

        // Try to jump from CREATED directly to DELIVERED
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson("/api/shipments/{$shipment->id}/tracking", [
                'status' => 'DELIVERED',
                'location_name' => 'Port of Rotterdam',
                'event_time' => Carbon::now()->toDateTimeString(),
                'description' => 'Illegal direct delivery attempt',
            ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
            ]);

        $this->assertStringContainsString('Invalid status transition', $response->json('message'));

        // Verify status remains CREATED
        $shipment->refresh();
        $this->assertEquals('CREATED', $shipment->current_status);
    }

    /**
     * Scenario 6: Deterministic ETA / Delay Rule:
     * - Verifies that EtaService evaluates delays against 12h threshold:
     *   - 18h delay (>=12h & <=24h) marks is_delayed = true with delay_severity = 'MEDIUM'.
     *   - 30h delay (>24h) marks is_delayed = true with delay_severity = 'HIGH'.
     *   - 5h delay (<12h threshold) marks is_delayed = false with delay_severity = null.
     */
    public function test_deterministic_eta_delay_rules_and_severity_classification(): void
    {
        $expectedArrival = Carbon::create(2026, 11, 20, 10, 0, 0);

        $shipment = Shipment::create([
            'shipment_number' => 'CT-2026-906',
            'customer_id' => $this->customer1->id,
            'carrier_id' => $this->carrier->id,
            'origin_location_id' => $this->origin->id,
            'destination_location_id' => $this->destination->id,
            'current_status' => 'IN_TRANSIT',
            'cargo_type' => 'Palm Oil',
            'total_weight' => 5000,
            'total_volume' => 80,
            'expected_departure' => $expectedArrival->copy()->subDays(20),
            'expected_arrival' => $expectedArrival,
            'is_delayed' => false,
        ]);

        $etaService = app(EtaService::class);

        // 1. Case A: 18 Hours Delay (Threshold = 12h -> is_delayed = true, severity = MEDIUM)
        $eta18h = $expectedArrival->copy()->addHours(18);
        $evaluatedA = $etaService->evaluateDelay($shipment, $eta18h, 'Bay of Bengal Monsoon', $this->operator);

        $this->assertTrue($evaluatedA->is_delayed);
        $this->assertEquals(18, $evaluatedA->delay_hours);
        $this->assertEquals('MEDIUM', $evaluatedA->delay_severity);

        // 2. Case B: 30 Hours Delay (> 24h -> is_delayed = true, severity = HIGH)
        $eta30h = $expectedArrival->copy()->addHours(30);
        $evaluatedB = $etaService->evaluateDelay($shipment, $eta30h, 'Suez Canal Port Congestion', $this->operator);

        $this->assertTrue($evaluatedB->is_delayed);
        $this->assertEquals(30, $evaluatedB->delay_hours);
        $this->assertEquals('HIGH', $evaluatedB->delay_severity);

        // 3. Case C: 5 Hours Delay (< 12h threshold -> is_delayed = false, severity = null)
        $eta5h = $expectedArrival->copy()->addHours(5);
        $evaluatedC = $etaService->evaluateDelay($shipment, $eta5h, 'Minor berth delay', $this->operator);

        $this->assertFalse($evaluatedC->is_delayed);
        $this->assertEquals(5, $evaluatedC->delay_hours);
        $this->assertNull($evaluatedC->delay_severity);
    }

    /**
     * Additional Scenario 7: Registration & Master Data Creation via API:
     * - Register a new customer via POST /api/register.
     */
    public function test_customer_registration_via_api(): void
    {
        $regResponse = $this->postJson('/api/register', [
            'name' => 'Budi Client',
            'email' => 'budi.client@export.test',
            'password' => 'secret1234',
            'password_confirmation' => 'secret1234',
            'company_name' => 'PT Budi Global',
            'phone' => '+6281234567890',
        ]);

        $regResponse->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'User registered successfully as Customer.',
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'budi.client@export.test',
            'role' => 'customer',
        ]);
    }

    /**
     * Additional Scenario 8: Admin creates Carrier via API.
     */
    public function test_admin_can_create_carrier_via_api(): void
    {
        $adminToken = $this->admin->createToken('admin_token')->plainTextToken;
        $carrierResponse = $this->withHeader('Authorization', 'Bearer ' . $adminToken)
            ->postJson('/api/carriers', [
                'name' => 'Evergreen Marine',
                'code' => 'EMCU',
                'type' => 'SEA',
                'contact_email' => 'ops@evergreen.test',
                'is_active' => true,
            ]);

        $carrierResponse->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Carrier created successfully.',
            ]);

        $this->assertDatabaseHas('carriers', ['code' => 'EMCU']);
    }

    /**
     * Additional Scenario 9: Customer receives 403 trying to create Carrier.
     */
    public function test_customer_forbidden_from_creating_carrier_via_api(): void
    {
        $customerToken = $this->customer1->createToken('cust_token')->plainTextToken;
        $custCarrierResponse = $this->withHeader('Authorization', 'Bearer ' . $customerToken)
            ->postJson('/api/carriers', [
                'name' => 'Hapag-Lloyd',
                'code' => 'HLCU',
                'type' => 'SEA',
            ]);

        $custCarrierResponse->assertStatus(403);
    }

    /**
     * Additional Scenario 10: Operational Incident / Exception Workflow:
     * - Operator reports CUSTOMS_HOLD on a shipment (status 201).
     * - Operator updates status to RESOLVED (status 200).
     */
    public function test_operator_reports_and_resolves_shipment_exception_via_api(): void
    {
        $shipment = Shipment::create([
            'shipment_number' => 'CT-2026-908',
            'customer_id' => $this->customer1->id,
            'carrier_id' => $this->carrier->id,
            'origin_location_id' => $this->origin->id,
            'destination_location_id' => $this->destination->id,
            'current_status' => 'IN_TRANSIT',
            'cargo_type' => 'Manufactured Goods',
            'total_weight' => 2500,
            'total_volume' => 35,
            'expected_departure' => Carbon::now()->subDays(5),
            'expected_arrival' => Carbon::now()->addDays(15),
        ]);

        $operatorToken = $this->operator->createToken('operator_token')->plainTextToken;

        // 1. Report CUSTOMS_HOLD exception
        $reportResponse = $this->withHeader('Authorization', 'Bearer ' . $operatorToken)
            ->postJson("/api/shipments/{$shipment->id}/exceptions", [
                'exception_type' => 'CUSTOMS_HOLD',
                'severity' => 'MEDIUM',
                'description' => 'Document audit hold at port clearance gate',
                'location_name' => 'Port of Rotterdam Customs Terminal',
            ]);

        $reportResponse->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Shipment exception reported successfully.',
            ]);

        $exceptionId = $reportResponse->json('data.id');

        $this->assertDatabaseHas('shipment_exceptions', [
            'id' => $exceptionId,
            'exception_type' => 'CUSTOMS_HOLD',
            'status' => 'OPEN',
        ]);

        // 2. Resolve exception
        $resolveResponse = $this->withHeader('Authorization', 'Bearer ' . $operatorToken)
            ->patchJson("/api/exceptions/{$exceptionId}", [
                'status' => 'RESOLVED',
            ]);

        $resolveResponse->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Exception status updated to RESOLVED.',
            ]);

        $this->assertDatabaseHas('shipment_exceptions', [
            'id' => $exceptionId,
            'status' => 'RESOLVED',
        ]);
    }
}
