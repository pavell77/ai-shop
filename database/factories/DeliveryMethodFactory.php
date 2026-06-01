<?php

namespace Database\Factories;

use App\Models\DeliveryMethod;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory target="_blank"<\App\Models\DeliveryMethod>
 */
class DeliveryMethodFactory extends Factory
{
    protected $model = DeliveryMethod::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Намагаємося взяти реальне ім'я, якщо ні — генеруємо фейк
        try {
            $name = $this->faker->unique()->randomElement([
                'Нова Пошта',
                'Самовивіз'
            ]);
        } catch (\OverflowException $e) {
            $name = $this->faker->word() . ' Delivery ' . $this->faker->unique()->numberBetween(1, 1000);
        }

        return [
            'name' => $name,
            'code' => \Illuminate\Support\Str::slug($name, '_'),
            'price' => $name === 'Самовивіз' ? 0.00 : 80.00,
            'is_active' => true,
        ];
    }
}