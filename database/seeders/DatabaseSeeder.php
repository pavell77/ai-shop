<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\DeliveryMethod;
use App\Models\PaymentMethod;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Запускаємо ізольовані класи-сідери через масив call
        $this->call([
            RoleSeeder::class,               // Базові ролі (admin, manager, user)
            CatalogSeeder::class,            // Категорії та товари
            NotificationTemplateSeeder::class, // Наші нові динамічні шаблони листів
        ]);

        // 2. Безпечне створення тестового користувача
        if (!User::where('email', 'test@example.com')->exists()) {
            User::factory()->create([
                'name' => 'Test User',
                'email' => 'test@example.com',
            ]);
        }

        // 3. Методи доставки (залишаємо тут, або пізніше теж винесемо в окремий DeliveryMethodSeeder)
        DeliveryMethod::firstOrCreate(
            ['code' => 'nova_poshta'],
            ['name' => 'Нова Пошта', 'price' => 80.00]
        );
        DeliveryMethod::firstOrCreate(
            ['code' => 'pickup'],
            ['name' => 'Самовивіз', 'price' => 0.00]
        );

        // 4. Платіжні драйвери
        PaymentMethod::firstOrCreate(['code' => 'wayforpay'], ['name' => 'WayForPay (Картка / Google Pay / Apple Pay)']);
        PaymentMethod::firstOrCreate(['code' => 'liqpay'], ['name' => 'LiqPay (Приват24)']);
        PaymentMethod::firstOrCreate(['code' => 'cod'], ['name' => 'Післяплата при отриманні']);
    }
}