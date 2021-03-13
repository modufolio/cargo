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
                'name' => 'Keuangan 1',
                'slug' => 'finance-1',
            ],
            [
                'menu_id' => $menu->id,
                'name' => 'Keuangan 2',
                'slug' => 'finance-2',
            ],
        ];
        Submenu::insert($submenu);
    }
}
