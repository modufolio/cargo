<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use DB;
use Illuminate\Support\Arr;

class DriverSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::beginTransaction();
        DB::table('drivers')->truncate();
        DB::table('drivers')->insert([
            [
                'type' => Arr::random(['internal','external']),
                'user_id' => 3,
                'status' => 'available',
                'active' => true
            ],
            [
                'type' => Arr::random(['internal','external']),
                'user_id' => 4,
                'status' => 'available',
                'active' => true,
            ],
            [
                'type' => Arr::random(['internal','external']),
                'user_id' => 7,
                'status' => 'available',
                'active' => true,
            ]
        ]);
        DB::commit();
    }
}
