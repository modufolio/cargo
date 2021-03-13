<?php

namespace Database\Seeders\Menus;

use Illuminate\Database\Seeder;
use App\Models\Menu;
use App\Models\Submenu;
use Carbon\Carbon;

class ResiMenuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $menu = new Menu;
        $menu->name = 'Resi';
        $menu->slug = 'resi';
        $menu->icon = 'resi';
        $menu->save();
        $submenu = [
            [
                'menu_id' => $menu->id,
                'name' => 'Resi 1',
                'slug' => 'resi-1',
            ],
            [
                'menu_id' => $menu->id,
                'name' => 'Resi 2',
                'slug' => 'resi-2',
            ],
        ];
        Submenu::insert($submenu);
    }
}
