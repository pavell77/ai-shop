<?php

namespace Database\Seeders;

use App\Models\NotificationTemplate;
use Illuminate\Database\Seeder;

class NotificationTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $templates = [
            [
                'code' => 'order_created_cod',
                'name' => 'Нове замовлення (Післяплата)',
                'subject' => 'Замовлення #{order_id} успішно прийнято!',
                'body' => '<h2>Вітаємо, {customer_name}!</h2><p>Дякуємо за покупку в Precinct 13. Ваше замовлення #{order_id} на суму {total_price} прийнято і буде відправлено на адресу: {address}. Оплата здійснюється при отриманні.</p>',
            ],
            [
                'code' => 'order_awaiting_payment',
                'name' => 'Очікування оплати (WayForPay)',
                'subject' => 'Замовлення #{order_id} сформовано. Очікуємо оплату',
                'body' => '<h2>Вітаємо, {customer_name}!</h2><p>Ваше замовлення #{order_id} на суму {total_price} успішно сформовано.</p><p>Для завершення оформлення, будь ласка, здійсніть оплату через платіжний шлюз банку. Якщо ви випадково закрили сторінку оплати, ви можете повернутися до неї в будь-який момент.</p>',
            ],
        ];

        foreach ($templates as $template) {
            // Використовуємо updateOrCreate, щоб сідер можна було запускати повторно без помилок Duplicate Entry
            NotificationTemplate::updateOrCreate(
                ['code' => $template['code']],
                [
                    'name' => $template['name'],
                    'subject' => $template['subject'],
                    'body' => $template['body'],
                ]
            );
        }
    }
}