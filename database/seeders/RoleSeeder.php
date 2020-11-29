<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $roles = [
            [
                'name' => 'Customer',
                'slug' => 'customer',
                'ranking' => 5,
                'features' => json_encode([1])
            ],
            [
                'name' => 'Marketing',
                'slug' => 'marketing',
                'ranking' => 3,
                'features' => json_encode([1])
            ],
            [
                'name' => 'Driver',
                'slug' => 'driver',
                'ranking' => 5,
                'features' => json_encode([1])
            ],
            [
                'name' => 'Konter',
                'slug' => 'konter',
                'ranking' => 2,
                'features' => json_encode([1])
            ],
            [
                'name' => 'Staff Outgoing',
                'slug' => 'staff-outgoing',
                'ranking' => 5,
                'features' => json_encode([1])
            ],
            [
                'name' => 'Finance',
                'slug' => 'finance',
                'ranking' => 2,
                'features' => json_encode([1])
            ],
            [
                'name' => 'Outgoing 3PL',
                'slug' => 'outgoing-3pl',
                'ranking' => 5,
                'features' => json_encode([1])
            ],
            [
                'name' => 'Driver 3PL',
                'slug' => 'driver-3pl',
                'ranking' => 5,
                'features' => json_encode([1])
            ],
            [
                'name' => 'Admin',
                'slug' => 'admin',
                'ranking' => 1,
                'features' => json_encode([1])
            ],
            [
                'name' => 'Guest',
                'slug' => 'guest',
                'ranking' => 5,
                'features' => json_encode([1])
            ]
        ];
        Role::insert($roles);
    }
}
