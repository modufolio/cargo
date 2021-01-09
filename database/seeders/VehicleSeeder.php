<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use DB;
use Illuminate\Support\Arr;

class VehicleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::beginTransaction();
        DB::table('vehicles')->truncate();
        DB::table('vehicles')->insert([
            [
                'driver_id' => Arr::random([3,4]),
                'name' => 'L 300',
                'license_plate' => 'B 2906 LMF',
                'type' => 'L 300',
                'max_volume' => 1000,
                'max_weight' => 1200,
            ],
            [
                'driver_id' => Arr::random([3,4]),
                'name' => 'KN 20',
                'license_plate' => 'B 9683 MXG',
                'type' => 'KN 20',
                'max_volume' => 900,
                'max_weight' => 1000,
            ],
            [
                'driver_id' => Arr::random([3,4]),
                'name' => 'C 200',
                'license_plate' => 'B 5125 PSW',
                'type' => 'C 200',
                'max_volume' => 500,
                'max_weight' => 800,
            ],
            [
                'driver_id' => Arr::random([3,4]),
                'name' => 'C 300',
                'license_plate' => 'B 1452 HUI',
                'type' => 'C 300',
                'max_volume' => 950,
                'max_weight' => 1200,
            ]
        ]);
        DB::commit();
    }
}
