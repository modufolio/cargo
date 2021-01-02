<?php

namespace Database\Seeders\Menus;

use Illuminate\Database\Seeder;
use App\Models\Menu;
use App\Models\Submenu;
use Carbon\Carbon;

class UserMenuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $menu = new Menu;
        $menu->name = 'User';
        $menu->slug = 'user';
        $menu->icon = 'pengguna';
        $menu->save();
        $submenu = [
            [
                'menu_id' => $menu->id,
                'name' => 'Daftar Pengguna',
                'slug' => 'list',
            ],
            [
                'menu_id' => $menu->id,
                'name' => 'Pengaturan Peran',
                'slug' => 'role',
            ],
        ];
        Submenu::insert($submenu);
    }
}
