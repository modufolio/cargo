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
            [
                'name'          => 'Satuan',
                'price'         => 1000,
                'created_at'    => Carbon::now('Asia/Jakarta'),
                'updated_at'    => Carbon::now('Asia/Jakarta'),
            ],
            [
                'name'          => 'Kilogram',
                'price'         => 1000,
                'created_at'    => Carbon::now('Asia/Jakarta'),
                'updated_at'    => Carbon::now('Asia/Jakarta'),
            ],
            [
                'name'          => 'Volume',
                'price'         => 1000,
                'created_at'    => Carbon::now('Asia/Jakarta'),
                'updated_at'    => Carbon::now('Asia/Jakarta'),
            ],
        ]);
        DB::commit();
    }
}
