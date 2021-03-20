<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use DB;
use Carbon\Carbon;

class UnitSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::beginTransaction();
        DB::table('units')->truncate();
        DB::table('units')->insert([
            // [
            //     'name'          => 'Satuan',
            //     'slug'          => 'Buah',
            //     'price'         => 1000,
            //     'created_at'    => Carbon::now('Asia/Jakarta'),
            //     'updated_at'    => Carbon::now('Asia/Jakarta'),
            // ],
            [
                'name'          => 'Kilogram',
                'slug'          => 'Kg',
                'price'         => 2000,
                'created_at'    => Carbon::now('Asia/Jakarta'),
                'updated_at'    => Carbon::now('Asia/Jakarta'),
            ],
            [
                'name'          => 'Volume',
                'slug'          => 'M3',
                'price'         => 3000,
                'created_at'    => Carbon::now('Asia/Jakarta'),
                'updated_at'    => Carbon::now('Asia/Jakarta'),
            ],
        ]);
        DB::commit();
    }
}
