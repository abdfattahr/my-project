<?php

namespace Database\Seeders;

use App\Models\MainCategorie;
use Illuminate\Database\Seeder;

class NewMainCategorieSeeder extends Seeder
{
    public function run()
    {
        $categories = [
            [
                'name' => 'الأطعمة الطازجة',
                'icon' => 'heroicon-o-shopping-cart',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'المشروبات الباردة',
                'icon' => 'heroicon-o-beaker',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'مستحضرات التجميل',
                'icon' => 'heroicon-o-heart',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'الأدوات الكهربائية',
                'icon' => 'heroicon-o-home',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'الأجهزة الذكية',
                'icon' => 'heroicon-o-device-phone-mobile',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'الملابس',
                'icon' => 'heroicon-o-shirt',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        MainCategorie::insert($categories);
    }
}