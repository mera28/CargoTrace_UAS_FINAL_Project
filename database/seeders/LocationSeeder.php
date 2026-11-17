<?php

namespace Database\Seeders;

use App\Models\Location;
use Illuminate\Database\Seeder;

class LocationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $locations = [
            [
                'name' => 'Port of Tanjung Priok',
                'code' => 'IDJKT',
                'city' => 'Jakarta',
                'country' => 'Indonesia',
                'region' => 'Southeast Asia',
                'latitude' => -6.1045,
                'longitude' => 106.8832,
            ],
            [
                'name' => 'Port of Singapore',
                'code' => 'SGSIN',
                'city' => 'Singapore',
                'country' => 'Singapore',
                'region' => 'Southeast Asia',
                'latitude' => 1.2644,
                'longitude' => 103.8223,
            ],
            [
                'name' => 'Port of Tanjung Pelepas',
                'code' => 'MYTPP',
                'city' => 'Johor',
                'country' => 'Malaysia',
                'region' => 'Southeast Asia',
                'latitude' => 1.3622,
                'longitude' => 103.5489,
            ],
            [
                'name' => 'Port of Rotterdam',
                'code' => 'NLRTM',
                'city' => 'Rotterdam',
                'country' => 'Netherlands',
                'region' => 'Europe',
                'latitude' => 51.9490,
                'longitude' => 4.1453,
            ],
            [
                'name' => 'Port of Hamburg',
                'code' => 'DEHAM',
                'city' => 'Hamburg',
                'country' => 'Germany',
                'region' => 'Europe',
                'latitude' => 53.5461,
                'longitude' => 9.9661,
            ],
            [
                'name' => 'Port of Antwerp',
                'code' => 'BEANR',
                'city' => 'Antwerp',
                'country' => 'Belgium',
                'region' => 'Europe',
                'latitude' => 51.2602,
                'longitude' => 4.4028,
            ],
        ];

        foreach ($locations as $location) {
            Location::updateOrCreate(['code' => $location['code']], $location);
        }
    }
}
