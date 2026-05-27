<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    /**
     * Ініціалізація та редирект на платіжну сторінку WayForPay
     */
    public function redirectToGateway(Order $order)
    {
        // Тимчасова візуальна заглушка для перевірки роутингу
        return "
            <div style='background: #1e293b; color: white; min-height: 100vh; display: flex; flex-direction: column; align-items: center; justify-content: center; font-family: sans-serif;'>
                <div style='background: #0f172a; padding: 2rem; rounded-size: 8px; border: 1px solid #334155; text-align: center; max-width: 500px;'>
                    <h2 style='color: #818cf8;'>💳 Перенаправлення на WayForPay...</h2>
                    <p>Готуємо безпечну оплату для замовлення <strong>#{$order->id}</strong></p>
                    <p>Сума: <span style='color: #34d399; font-weight: bold;'>{$order->total_price} ₴</span></p>
                    <div style='margin-top: 1.5rem; font-size: 0.85rem; color: #94a3b8;'>
                        Тут ми будемо рендерити приховану HTML-форму, яка автоматично через JS зробить submit на шлюз банку.
                    </div>
                </div>
            </div>
        ";
    }
}