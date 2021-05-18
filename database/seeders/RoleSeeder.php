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
                'privilleges' => json_encode([])
            ],
            [
                'name' => 'Marketing',
                'slug' => 'marketing',
                'ranking' => 3,
                'privilleges' => json_encode(["menu_4","submenu_9","submenu_10"])
            ],
            [
                'name' => 'Driver',
                'slug' => 'driver',
                'ranking' => 5,
                'privilleges' => json_encode([])
            ],
            [
                'name' => 'Konter',
                'slug' => 'konter',
                'ranking' => 2,
                'privilleges' => json_encode([])
            ],
            [
                'name' => 'Staff Outgoing',
                'slug' => 'staff-outgoing',
                'ranking' => 5,
                'privilleges' => json_encode(["menu_4","submenu_9","submenu_10"])
            ],
            [
                'name' => 'Finance',
                'slug' => 'finance',
                'ranking' => 2,
                'privilleges' => json_encode(["menu_4","submenu_9","submenu_10"])
            ],
            [
                'name' => 'Outgoing 3PL',
                'slug' => 'outgoing-3pl',
                'ranking' => 5,
                'privilleges' => json_encode([])
            ],
            [
                'name' => 'Driver 3PL',
                'slug' => 'driver-3pl',
                'ranking' => 5,
                'privilleges' => json_encode([])
            ],
            [
                'name' => 'Admin',
                'slug' => 'admin',
                'ranking' => 1,
                'privilleges' => json_encode(["menu_1","menu_2","menu_3","menu_4","menu_5","menu_6","menu_7","menu_8","submenu_1","submenu_2","submenu_3","submenu_4","submenu_5","submenu_6","submenu_7","submenu_8","submenu_9","submenu_10","submenu_11","submenu_12","submenu_13","submenu_14","submenu_15","submenu_16","submenu_17","submenu_18","submenu_19"])
            ]
        ];
        Role::insert($roles);
        DB::commit();
    }
}
