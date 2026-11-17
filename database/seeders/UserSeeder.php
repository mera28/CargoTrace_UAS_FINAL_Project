<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = [
            [
                'name' => 'Admin CargoTrace',
                'email' => 'admin@cargotrace.test',
                'password' => Hash::make('password123'),
                'role' => 'admin',
                'company_name' => 'CargoTrace HQ',
                'phone' => '+62811000001',
            ],
            [
                'name' => 'Budi Santoso',
                'email' => 'operator@cargotrace.test',
                'password' => Hash::make('password123'),
                'role' => 'operator',
                'company_name' => 'CargoTrace Operations',
                'phone' => '+62811000002',
            ],
            [
                'name' => 'Siti Rahma',
                'email' => 'customer1@cargotrace.test',
                'password' => Hash::make('password123'),
                'role' => 'customer',
                'company_name' => 'PT Nusantara Export',
                'phone' => '+62811000003',
            ],
            [
                'name' => 'Hendrik Van Der Berg',
                'email' => 'customer2@cargotrace.test',
                'password' => Hash::make('password123'),
                'role' => 'customer',
                'company_name' => 'EuroTrade Imports BV',
                'phone' => '+31612345678',
            ],
        ];

        foreach ($users as $user) {
            User::updateOrCreate(['email' => $user['email']], $user);
        }
    }
}
