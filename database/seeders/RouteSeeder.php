<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use DB;
use Carbon\Carbon;

class RouteSeeder extends Seeder
{

    /**
     * Run the database seeds.
     *
     * fleetID:
     * 1 => reguler
     * 2 => express
     * 3 => udara
     * 4 => darat / laut
     * @return void
     */
    public function run()
    {
        DB::beginTransaction();
        DB::table('routes')->truncate();
        DB::table('routes')->insert([
            [
                'fleet_id'              => 4,
                'origin'                => 'KOTA SURABAYA',
                'destination'           => 'KOTA MEDAN',
                'destination_island'    => 'SUMATERA',
                'price'                 => 4000,
                'minimum_weight'        => 100,
            ],
            [
                'fleet_id'              => 3,
                'origin'                => 'KOTA SURABAYA',
                'destination'           => 'KOTA MEDAN',
                'destination_island'    => 'SUMATERA',
                'price'                 => 33500,
                'minimum_weight'        => 15,
            ],
            [
                'fleet_id'              => 4,
                'origin'                => 'KOTA SURABAYA',
                'destination'           => 'KOTA BATAM',
                'destination_island'    => 'SUMATERA',
                'price'                 => 5000,
                'minimum_weight'        => 100,
            ],
            [
                'fleet_id'              => 3,
                'origin'                => 'KOTA SURABAYA',
                'destination'           => 'KOTA BATAM',
                'destination_island'    => 'SUMATERA',
                'price'                 => 34000,
                'minimum_weight'        => 10,
            ],
            [
                'fleet_id'              => 4,
                'origin'                => 'KOTA SURABAYA',
                'destination'           => 'KOTA PEKANBARU',
                'destination_island'    => 'SUMATERA',
                'price'                 => 4000,
                'minimum_weight'        => 100,
            ],
            [
                'fleet_id'              => 3,
                'origin'                => 'KOTA SURABAYA',
                'destination'           => 'KOTA PEKANBARU',
                'destination_island'    => 'SUMATERA',
                'price'                 => 29500,
                'minimum_weight'        => 15,
            ],
            [
                'fleet_id'              => 4,
                'origin'                => 'KOTA SURABAYA',
                'destination'           => 'DUMAI KOTA',
                'destination_island'    => 'SUMATERA',
                'price'                 => 8000,
                'minimum_weight'        => 100,
            ],
            [
                'fleet_id'              => 4,
                'origin'                => 'KOTA SURABAYA',
                'destination'           => 'SIAK',
                'destination_island'    => 'SUMATERA',
                'price'                 => 9500,
                'minimum_weight'        => 100,
            ],
            [
                'fleet_id'              => 4,
                'origin'                => 'KOTA SURABAYA',
                'destination'           => 'PANGKALAN KERINCI',
                'destination_island'    => 'SUMATERA',
                'price'                 => 8000,
                'minimum_weight'        => 100,
            ],
            [
                'fleet_id'              => 4,
                'origin'                => 'KOTA SURABAYA',
                'destination'           => 'RENGAT',
                'destination_island'    => 'SUMATERA',
                'price'                 => 9000,
                'minimum_weight'        => 100,
            ],
            [
                'fleet_id'              => 4,
                'origin'                => 'KOTA SURABAYA',
                'destination'           => 'TEMBILAHAN',
                'destination_island'    => 'SUMATERA',
                'price'                 => 10000,
                'minimum_weight'        => 100,
            ],
            [
                'fleet_id'              => 4,
                'origin'                => 'KOTA SURABAYA',
                'destination'           => 'KABUPATEN KAMPAR',
                'destination_island'    => 'SUMATERA',
                'price'                 => 10000,
                'minimum_weight'        => 100,
            ],
            [
                'fleet_id'              => 4,
                'origin'                => 'KOTA SURABAYA',
                'destination'           => 'LIRIK',
                'destination_island'    => 'SUMATERA',
                'price'                 => 8000,
                'minimum_weight'        => 100,
            ],
            [
                'fleet_id'              => 4,
                'origin'                => 'KOTA SURABAYA',
                'destination'           => 'SUNGAI PAKNING',
                'destination_island'    => 'SUMATERA',
                'price'                 => 8000,
                'minimum_weight'        => 100,
            ],
        ]);
        DB::commit();
    }
}
