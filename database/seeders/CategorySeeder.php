<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Kopi & Teh'],
            ['name' => 'Nasi & Lauk'],
            ['name' => 'Bakso & Mie'],
            ['name' => 'Cepat Saji'],
            ['name' => 'Makanan Tradisional'],
            ['name' => 'Seafood'],
            ['name' => 'Camilan & Dessert'],
            ['name' => 'Aneka Minuman'],
        ];

        foreach ($categories as $cat) {
            Category::firstOrCreate(
                ['name' => $cat['name']],
                ['icon_path' => null]
            );
        }
    }
}
