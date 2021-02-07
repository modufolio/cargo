<?php

namespace Database\Seeders\Menus;

use Illuminate\Database\Seeder;
use App\Models\Menu;
use App\Models\Submenu;
use Carbon\Carbon;

class OrderMenuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $menu = new Menu;
        $menu->name = 'Pengiriman';
        $menu->slug = 'order';
        $menu->icon = 'order';
        $menu->save();
        $submenu = [
            [
                'menu_id' => $menu->id,
                'name' => 'Pickup',
                'slug' => 'pickup',
            ],
            [
                'menu_id' => $menu->id,
                'name' => 'Drop',
                'slug' => 'drop',
            ],
        ];
        Submenu::insert($submenu);
    }
}
