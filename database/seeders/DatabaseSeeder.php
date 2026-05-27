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

        // 2. Безпечне створення тестового користувача (тільки якщо його немає)
        if (!User::where('email', 'test@example.com')->exists()) {
            User::factory()->create([
                'name' => 'Test User',
                'email' => 'test@example.com',
            ]);
        }

        // Наші методи доставки (захищені від дублікатів через code)
        DeliveryMethod::firstOrCreate(
            ['code' => 'nova_poshta'],
            ['name' => 'Нова Пошта', 'price' => 80.00]
        );
        DeliveryMethod::firstOrCreate(
            ['code' => 'pickup'],
            ['name' => 'Самовивіз', 'price' => 0.00]
        );

        // Наші платіжні драйвери (WayForPay замість Monobank)
        PaymentMethod::firstOrCreate(['code' => 'wayforpay'], ['name' => 'WayForPay (Картка / Google Pay / Apple Pay)']);
        PaymentMethod::firstOrCreate(['code' => 'liqpay'], ['name' => 'LiqPay (Приват24)']);
        PaymentMethod::firstOrCreate(['code' => 'cod'], ['name' => 'Післяплата при отриманні']);

        // Шаблон листа за замовчуванням (захищений через code)
        NotificationTemplate::firstOrCreate(
            ['code' => 'order_created'],
            [
                'name' => 'Створення замовлення',
                'subject' => 'Ваше замовлення №{order_id} успішно створено!',
                'body' => '<h1>Дякуємо за покупку!</h1><p>Сума до сплати: {total_price} грн.</p>'
            ]
        );

        // 3. Підключаємо наш новий каталог товарів!
        $this->call([
            CatalogSeeder::class,
        ]);
    }
}