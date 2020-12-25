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
                'destination_city'      => 'KOTA MEDAN',
                'destination_district'  => 'MEDAN KOTA',
                'destination_island'    => 'SUMATERA',
                'price'                 => 4000,
                'minimum_weight'        => 100,
            ],
            [
                'fleet_id'              => 3,
                'origin'                => 'KOTA SURABAYA',
                'destination_city'      => 'KOTA MEDAN',
                'destination_district'  => 'MEDAN KOTA',
                'destination_island'    => 'SUMATERA',
                'price'                 => 33500,
                'minimum_weight'        => 15,
            ],
            [
                'fleet_id'              => 4,
                'origin'                => 'KOTA SURABAYA',
                'destination_city'      => 'KOTA BATAM',
                'destination_district'  => 'BATAM KOTA',
                'destination_island'    => 'SUMATERA',
                'price'                 => 5000,
                'minimum_weight'        => 100,
            ],
            [
                'fleet_id'              => 3,
                'origin'                => 'KOTA SURABAYA',
                'destination_city'      => 'KOTA BATAM',
                'destination_district'  => 'BATAM KOTA',
                'destination_island'    => 'SUMATERA',
                'price'                 => 34000,
                'minimum_weight'        => 10,
            ],
            [
                'fleet_id'              => 4,
                'origin'                => 'KOTA SURABAYA',
                'destination_city'      => 'KOTA PEKANBARU',
                'destination_district'  => 'PEKANBARU KOTA',
                'destination_island'    => 'SUMATERA',
                'price'                 => 4000,
                'minimum_weight'        => 100,
            ],
            [
                'fleet_id'              => 3,
                'origin'                => 'KOTA SURABAYA',
                'destination_city'      => 'KOTA PEKANBARU',
                'destination_district'  => 'PEKANBARU KOTA',
                'destination_island'    => 'SUMATERA',
                'price'                 => 29500,
                'minimum_weight'        => 15,
            ],
            [
                'fleet_id'              => 4,
                'origin'                => 'KOTA SURABAYA',
                'destination_city'      => 'KOTA D U M A I',
                'destination_district'  => 'DUMAI KOTA',
                'destination_island'    => 'SUMATERA',
                'price'                 => 8000,
                'minimum_weight'        => 100,
            ],
            [
                'fleet_id'              => 4,
                'origin'                => 'KOTA SURABAYA',
                'destination_city'      => 'KABUPATEN S I A K',
                'destination_district'  => 'SIAK',
                'destination_island'    => 'SUMATERA',
                'price'                 => 9500,
                'minimum_weight'        => 100,
            ],
            [
                'fleet_id'              => 4,
                'origin'                => 'KOTA SURABAYA',
                'destination_city'      => 'KABUPATEN PELALAWAN',
                'destination_district'  => 'PANGKALAN KERINCI',
                'destination_island'    => 'SUMATERA',
                'price'                 => 8000,
                'minimum_weight'        => 100,
            ],
            [
                'fleet_id'              => 4,
                'origin'                => 'KOTA SURABAYA',
                'destination_city'      => 'KABUPATEN INDRAGIRI HULU',
                'destination_district'  => 'RENGAT',
                'destination_island'    => 'SUMATERA',
                'price'                 => 9000,
                'minimum_weight'        => 100,
            ],
            [
                'fleet_id'              => 4,
                'origin'                => 'KOTA SURABAYA',
                'destination_city'      => 'KABUPATEN INDRAGIRI HILIR',
                'destination_district'  => 'TEMBILAHAN',
                'destination_island'    => 'SUMATERA',
                'price'                 => 10000,
                'minimum_weight'        => 100,
            ],
            [
                'fleet_id'              => 4,
                'origin'                => 'KOTA SURABAYA',
                'destination_city'      => 'KABUPATEN KAMPAR',
                'destination_district'  => 'KAMPAR',
                'destination_island'    => 'SUMATERA',
                'price'                 => 10000,
                'minimum_weight'        => 100,
            ],
            [
                'fleet_id'              => 4,
                'origin'                => 'KOTA SURABAYA',
                'destination_city'      => 'BUKIT BATU',
                'destination_district'  => 'SUNGAI PAKNING',
                'destination_island'    => 'SUMATERA',
                'price'                 => 8000,
                'minimum_weight'        => 100,
            ],
        ]);
        DB::commit();
    }
}
