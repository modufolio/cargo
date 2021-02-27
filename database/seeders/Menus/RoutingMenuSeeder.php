<?php

namespace Database\Seeders\Menus;

use Illuminate\Database\Seeder;
use App\Models\Menu;
use App\Models\Submenu;
use Carbon\Carbon;

class RoutingMenuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $menu = new Menu;
        $menu->name = 'Rute';
        $menu->slug = 'routing';
        $menu->icon = 'routing';
        $menu->save();
        $submenu = [
            [
                'menu_id' => $menu->id,
                'name' => 'Pickup Plan',
                'slug' => 'pickup-plan',
            ],
            [
                'menu_id' => $menu->id,
                'name' => 'Shipment Plan',
                'slug' => 'shipment-plan',
            ],
            [
                'menu_id' => $menu->id,
                'name' => 'Po Pickup Plan',
                'slug' => 'po-pickup-plan',
            ],
            [
                'menu_id' => $menu->id,
                'name' => 'Po of Delivery',
                'slug' => 'po-delivery',
            ],
        ];
        Submenu::insert($submenu);
    }
}
