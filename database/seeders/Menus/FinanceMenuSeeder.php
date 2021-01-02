<?php

namespace Database\Seeders\Menus;

use Illuminate\Database\Seeder;
use App\Models\Menu;
use App\Models\Submenu;
use Carbon\Carbon;

class FinanceMenuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $menu = new Menu;
        $menu->name = 'Keuangan';
        $menu->slug = 'finance';
        $menu->icon = 'keuangan';
        $menu->save();
        $submenu = [
            [
                'menu_id' => $menu->id,
                'name' => 'Rute',
                'slug' => 'finance-1',
            ],
            [
                'menu_id' => $menu->id,
                'name' => 'Cabang',
                'slug' => 'finance-2',
            ],
            [
                'menu_id' => $menu->id,
                'name' => 'Satuan',
                'slug' => 'finance-3',
            ],
            [
                'menu_id' => $menu->id,
                'name' => 'Armada',
                'slug' => 'finance-4',
            ],
            [
                'menu_id' => $menu->id,
                'name' => 'Promo',
                'slug' => 'finance-5',
            ],
        ];
        Submenu::insert($submenu);
    }
}
