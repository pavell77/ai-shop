<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = ucfirst($this->faker->unique()->words(3, true));
        $costPrice = $this->faker->randomFloat(2, 15, 450); // Собівартість закупівлі
        $price = $costPrice * $this->faker->randomFloat(2, 1.25, 1.75); // Ціна продажу з націнкою ERP

        return [
            'category_id' => Category::factory(), // Зв'язує з новою категорією за дефолтом
            'name' => $name,
            'slug' => Str::slug($name),
            'sku' => strtoupper($this->faker->unique()->bothify('??-#####')), // Наприклад: AI-94821
            'description' => $this->faker->paragraphs(2, true),
            // Імітуємо збереження файлу в окрему теку товарів
            'image_path' => 'products/product_' . $this->faker->numberBetween(1, 50) . '.jpg',
            'cost_price' => round($costPrice, 2),
            'price' => round($price, 2),
            'quantity' => $this->faker->numberBetween(0, 150),
            'is_visible' => true,
        ];
    }
}