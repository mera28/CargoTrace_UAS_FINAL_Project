<?php

namespace Database\Seeders;

use App\Models\Carrier;
use Illuminate\Database\Seeder;

class CarrierSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $carriers = [
            [
                'name' => 'Maersk Line',
                'code' => 'MAEU',
                'type' => 'SEA',
                'contact_email' => 'support@maersk.com',
                'is_active' => true,
            ],
            [
                'name' => 'Mediterranean Shipping Company',
                'code' => 'MSCU',
                'type' => 'SEA',
                'contact_email' => 'info@msc.com',
                'is_active' => true,
            ],
            [
                'name' => 'CMA CGM',
                'code' => 'CMDU',
                'type' => 'SEA',
                'contact_email' => 'service@cma-cgm.com',
                'is_active' => true,
            ],
            [
                'name' => 'DHL Global Forwarding',
                'code' => 'DHLG',
                'type' => 'AIR',
                'contact_email' => 'cargo@dhl.com',
                'is_active' => true,
            ],
        ];

        foreach ($carriers as $carrier) {
            Carrier::updateOrCreate(['code' => $carrier['code']], $carrier);
        }
    }
}
