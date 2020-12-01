<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Feature;
use DB;

class FeatureSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::beginTransaction();
        DB::table('features')->truncate();
        $features = [
            [
                'name' => 'Reporting',
                'slug' => 'reporting',
            ]
        ];
        Feature::insert($features);
        DB::commit();
    }
}
