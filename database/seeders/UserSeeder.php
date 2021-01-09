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
            [
                'name' => 'ival',
                'email' => 'ivalrival95@gmail.com',
                'password' => bcrypt('ival1234'),
                'role_id' => 9,
                'username' => 'ival',
            ],
            [
                'name' => 'viky',
                'email' => 'vikyyahya.id@gmail.com',
                'password' => bcrypt('aaaaaaaa1'),
                'role_id' => 1,
                'username' => 'vikyyahya',
            ],
            [
                'name' => 'driver one',
                'email' => 'driver1@gmail.com',
                'password' => bcrypt('driver'),
                'role_id' => 3,
                'username' => 'driver_one',
            ],
            [
                'name' => 'driver two',
                'email' => 'driver2@gmail.com',
                'password' => bcrypt('driver'),
                'role_id' => 3,
                'username' => 'driver_two',
            ]
        ]);
        DB::commit();
    }
}
