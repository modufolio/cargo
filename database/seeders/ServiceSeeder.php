<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use DB;
use Carbon\Carbon;

class ServiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::beginTransaction();
        DB::table('services')->truncate();
        DB::table('services')->insert([
            [
                'name'          => 'Kayu',
                'price'         => 1000,
                'created_at'    => Carbon::now('Asia/Jakarta'),
                'updated_at'    => Carbon::now('Asia/Jakarta'),
            ],
            [
                'name'          => 'Bubble Wrap',
                'price'         => 1000,
                'created_at'    => Carbon::now('Asia/Jakarta'),
                'updated_at'    => Carbon::now('Asia/Jakarta'),
            ],
            [
                'name'          => 'Tanpa Layanan',
                'price'         => 0,
                'created_at'    => Carbon::now('Asia/Jakarta'),
                'updated_at'    => Carbon::now('Asia/Jakarta'),
            ]
        ]);
        DB::commit();
    }
}
