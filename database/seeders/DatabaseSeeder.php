<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Database\Seeders\RoleSeeder;
use Database\Seeders\FeatureSeeder;
use Database\Seeders\UserSeeder;
use Database\Seeders\FeatureRoleSeeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call(
            [
                RoleSeeder::class,
                FeatureSeeder::class,
                UserSeeder::class,
                FeatureRoleSeeder::class,
            ]
        );
    }
}
