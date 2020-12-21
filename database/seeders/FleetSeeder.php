<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use DB;
use Carbon\Carbon;

class FleetSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::beginTransaction();
        DB::table('fleets')->truncate();
        DB::table('fleets')->insert([
            [
                'type'          => 'Reguler',
                'icon'          => 'images/fleets/reguler.svg',
                'slug'          => 'reguler',
                'created_at'    => Carbon::now('Asia/Jakarta'),
                'updated_at'    => Carbon::now('Asia/Jakarta'),
            ],
            [
                'type'          => 'Express',
                'icon'          => 'images/fleets/express.svg',
                'slug'          => 'express',
                'created_at'    => Carbon::now('Asia/Jakarta'),
                'updated_at'    => Carbon::now('Asia/Jakarta'),
            ],
            [
                'type'          => 'Udara',
                'icon'          => 'images/fleets/udara.svg',
                'slug'          => 'udara',
                'created_at'    => Carbon::now('Asia/Jakarta'),
                'updated_at'    => Carbon::now('Asia/Jakarta'),
            ],
            [
                'type'          => 'Darat',
                'icon'          => 'images/fleets/darat.svg',
                'slug'          => 'darat',
                'created_at'    => Carbon::now('Asia/Jakarta'),
                'updated_at'    => Carbon::now('Asia/Jakarta'),
            ]
        ]);
        DB::commit();
    }
}
