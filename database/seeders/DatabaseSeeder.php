<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\DeliveryMethod;
use App\Models\PaymentMethod;
use App\Models\NotificationTemplate;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Спочатку створюємо базові ролі (admin, manager, user)
        $this->call([
            RoleSeeder::class,
        ]);

        // 2. Твій існуючий тестовий користувач для входу в адмінку
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        // Наші методи доставки
        DeliveryMethod::create(['name' => 'Нова Пошта', 'code' => 'nova_poshta', 'price' => 80.00]);
        DeliveryMethod::create(['name' => 'Самовивіз', 'code' => 'pickup', 'price' => 0.00]);

        // Наші платіжні драйвери
        PaymentMethod::create(['name' => 'Monobank (Картка / Apple Pay)', 'code' => 'monobank']);
        PaymentMethod::create(['name' => 'LiqPay (Приват24)', 'code' => 'liqpay']);
        PaymentMethod::create(['name' => 'Післяплата при отриманні', 'code' => 'cod']);

        // Шаблон листа за замовчуванням
        NotificationTemplate::create([
            'code' => 'order_created',
            'name' => 'Створення замовлення',
            'subject' => 'Ваше замовлення №{order_id} успішно створено!',
            'body' => '<h1>Дякуємо за покупку!</h1><p>Сума до сплати: {total_price} грн.</p>'
        ]);
    }
}