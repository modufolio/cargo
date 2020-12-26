<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use DB;
use Carbon\Carbon;

class PromoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::beginTransaction();
        DB::table('promos')->truncate();
        DB::table('promos')->insert([
            [
                'discount'      => 20,
                'discount_max'  => 10000,
                'min_value'     => 250000,
                'start_at'      => Carbon::now('Asia/Jakarta'),
                'end_at'        => Carbon::now('Asia/Jakarta')->addWeek(3),
                'max_used'      => 10,
                'description'   => 'Promo Year End',
                'code'          => 'Y34R3ND',
                'terms'         => 'Term And Condition'
            ],
            [
                'discount'      => 15,
                'discount_max'  => 20000,
                'min_value'     => 500000,
                'start_at'      => Carbon::now('Asia/Jakarta'),
                'end_at'        => Carbon::now('Asia/Jakarta')->addWeek(3),
                'max_used'      => 10,
                'description'   => 'Promo Year End',
                'code'          => 'Y34R3ND2',
                'terms'         => 'Term And Condition'
            ],
            [
                'discount'      => 50,
                'discount_max'  => 10000,
                'min_value'     => 150000,
                'start_at'      => Carbon::now('Asia/Jakarta'),
                'end_at'        => Carbon::now('Asia/Jakarta')->addWeek(3),
                'max_used'      => 10,
                'description'   => 'Promo Year End',
                'code'          => 'Y34R3ND3',
                'terms'         => 'Term And Condition'
            ],
            [
                'discount'      => 15,
                'discount_max'  => 50000,
                'min_value'     => 1000000,
                'start_at'      => Carbon::now('Asia/Jakarta'),
                'end_at'        => Carbon::now('Asia/Jakarta')->addWeek(3),
                'max_used'      => 10,
                'description'   => 'Promo Year End',
                'code'          => 'Y34R3ND4',
                'terms'         => 'Term And Condition'
            ],
        ]);
        DB::commit();
    }
}
