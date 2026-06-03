<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Console\Seeds\WithoutModelEvents; // Залишаємо імпорт
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CatalogSeeder extends Seeder
{
    use WithoutModelEvents; // Підключаємо трейт всередині класу!

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            ['name' => 'Нейромережі та Моделі', 'description' => 'Готові локальні та хмарні рішення для розробників.'],
            ['name' => 'ШІ Агенти', 'description' => 'Автономні помічники для автоматизації рутини.'],
            ['name' => 'Промпти та Налаштування', 'description' => 'Оптимізовані інструкції для LLM моделей.'],
            ['name' => 'Залізо для ШІ', 'description' => 'Обладнання та девайси для локального запуску мереж.'],
        ];

        foreach ($categories as $catData) {
            $category = Category::create([
                'name' => $catData['name'],
                'slug' => Str::slug($catData['name']),
                'description' => $catData['description'],
                'is_active' => true,
            ]);

            // Фабрика створить товари без виклику зайвих івентів моделей
            Product::factory()->count(6)->create([
                'category_id' => $category->id,
            ]);
        }
    }
}