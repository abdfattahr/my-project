<?php

namespace Database\Seeders;

use App\Models\MainCategorie;
use App\Models\Subcategorie;
use Illuminate\Database\Seeder;

class NewSubcategorieSeeder extends Seeder
{
    public function run()
    {
        $mainCategories = MainCategorie::pluck('id', 'name')->toArray();

        $subcategories = [
            [
                'main_category_id' => $mainCategories['الأطعمة الطازجة'],
                'name' => 'الفواكه',
                'icon' => 'heroicon-o-apple',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'main_category_id' => $mainCategories['الأطعمة الطازجة'],
                'name' => 'الخضروات',
                'icon' => 'heroicon-o-carrot',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'main_category_id' => $mainCategories['المشروبات الباردة'],
                'name' => 'المياه الغازية',
                'icon' => 'heroicon-o-cup',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'main_category_id' => $mainCategories['مستحضرات التجميل'],
                'name' => 'العناية بالشعر',
                'icon' => 'heroicon-o-face-smile',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'main_category_id' => $mainCategories['الأدوات الكهربائية'],
                'name' => 'أدوات المطبخ',
                'icon' => 'heroicon-o-sparkles',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'main_category_id' => $mainCategories['الأجهزة الذكية'],
                'name' => 'الهواتف الذكية',
                'icon' => 'heroicon-o-device-phone-mobile',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'main_category_id' => $mainCategories['الملابس'],
                'name' => 'ملابس الأطفال',
                'icon' => 'heroicon-o-shirt',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        Subcategorie::insert($subcategories);
    }
}