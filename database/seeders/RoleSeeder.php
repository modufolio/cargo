<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use DB;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::beginTransaction();
        DB::table('roles')->truncate();
        $roles = [
            [
                'name' => 'Customer',
                'slug' => 'customer',
                'ranking' => 5,
            ],
            [
                'name' => 'Marketing',
                'slug' => 'marketing',
                'ranking' => 3,
            ],
            [
                'name' => 'Driver',
                'slug' => 'driver',
                'ranking' => 5,
            ],
            [
                'name' => 'Konter',
                'slug' => 'konter',
                'ranking' => 2,
            ],
            [
                'name' => 'Staff Outgoing',
                'slug' => 'staff-outgoing',
                'ranking' => 5,
            ],
            [
                'name' => 'Finance',
                'slug' => 'finance',
                'ranking' => 2,
            ],
            [
                'name' => 'Outgoing 3PL',
                'slug' => 'outgoing-3pl',
                'ranking' => 5,
            ],
            [
                'name' => 'Driver 3PL',
                'slug' => 'driver-3pl',
                'ranking' => 5,
            ],
            [
                'name' => 'Admin',
                'slug' => 'admin',
                'ranking' => 1,
            ],
            [
                'name' => 'Guest',
                'slug' => 'guest',
                'ranking' => 5,
            ]
        ];
        Role::insert($roles);
        DB::commit();
    }
}
