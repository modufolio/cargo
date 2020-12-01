<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use DB;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::beginTransaction();
        DB::table('users')->truncate();
        DB::table('users')->insert([
            'name' => 'ival',
            'email' => 'ival@test.com',
            'password' => bcrypt('ival1234'),
            'role_id' => 1
        ]);
        DB::commit();
    }
}
