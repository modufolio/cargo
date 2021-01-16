<?php

namespace Database\Seeders\Menus;

use Illuminate\Database\Seeder;
use App\Models\Menu;
use App\Models\Submenu;
use Carbon\Carbon;

class MasterDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $menu = new Menu;
        $menu->name = 'Master Data';
        $menu->slug = 'master-data';
        $menu->icon = 'master-data';
        $menu->save();
        $submenu = [
            [
                'menu_id' => $menu->id,
                'name' => 'Rute',
                'slug' => 'route',
            ],
            [
                'menu_id' => $menu->id,
                'name' => 'Cabang',
                'slug' => 'branch',
            ],
            [
                'menu_id' => $menu->id,
                'name' => 'Satuan',
                'slug' => 'unit',
            ],
            [
                'menu_id' => $menu->id,
                'name' => 'Armada',
                'slug' => 'fleet',
            ],
            [
                'menu_id' => $menu->id,
                'name' => 'Promo',
                'slug' => 'promo',
            ],
            [
                'menu_id' => $menu->id,
                'name' => 'Kendaraan',
                'slug' => 'vehicle',
            ],
            [
                'menu_id' => $menu->id,
                'name' => 'Driver',
                'slug' => 'driver',
            ],
        ];
        Submenu::insert($submenu);
    }
}
