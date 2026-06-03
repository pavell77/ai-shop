<?php

namespace Database\Factories;

use App\Models\PaymentMethod;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PaymentMethod>
 */
class PaymentMethodFactory extends Factory
{
    protected $model = PaymentMethod::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        try {
            $name = $this->faker->unique()->randomElement([
                'WayForPay',
                'LiqPay',
                'Післяплата при отриманні'
            ]);
        } catch (\OverflowException $e) {
            $name = $this->faker->word() . ' Payment ' . $this->faker->unique()->numberBetween(1, 1000);
        }

        return [
            'name' => $name,
            'code' => \Illuminate\Support\Str::slug($name, '_'),
            'is_active' => true,
        ];
    }
}