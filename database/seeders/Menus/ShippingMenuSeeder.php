<?php

namespace Database\Seeders\Menus;

use Illuminate\Database\Seeder;
use App\Models\Menu;
use App\Models\Submenu;
use Carbon\Carbon;

class ShippingMenuSeeder extends Seeder
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
        $menu->slug = 'shipping';
        $menu->icon = 'pengiriman';
        $menu->save();
        $submenu = [
            [
                'menu_id' => $menu->id,
                'name' => 'Daftar Pengguna',
                'slug' => 'shipping-1',
            ],
            [
                'menu_id' => $menu->id,
                'name' => 'Pengaturan Peran',
                'slug' => 'shipping-2',
            ],
        ];
        Submenu::insert($submenu);
    }
}
