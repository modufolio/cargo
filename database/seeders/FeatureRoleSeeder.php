<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use DB;
use Exception;

class FeatureRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::beginTransaction();
        DB::table('feature_role')->truncate();
        try {
            DB::table('feature_role')->insert(
                ['role_id' => 9, 'feature_id' => 1],
                ['role_id' => 9, 'feature_id' => 2]
            );
            DB::commit();
        } catch (Exception $e) {
            DB::rollback();
            dd($e);
        }
    }
}
