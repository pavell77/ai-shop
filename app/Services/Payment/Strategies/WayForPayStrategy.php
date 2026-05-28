<?php

namespace App\Services\Payment\Strategies;

use App\Contracts\PaymentStrategyInterface;
use App\Models\Order;
use App\Models\Transaction;
use App\Services\Notification\NotificationService;
use App\Services\Payment\PaymentResult;

class WayForPayStrategy implements PaymentStrategyInterface
{
    protected NotificationService $notifier;

    public function __construct(NotificationService $notifier)
    {
        $this->notifier = $notifier;
    }

    public function process(Order $order): PaymentResult
    {
        // 1. Оновлюємо статус замовлення
        $order->update(['status' => 'pending_payment']);

        // 2. Створюємо первинну транзакцію в базі
        Transaction::create([
            'order_id'          => $order->id,
            'payment_method_id' => $order->payment_method_id,
            'amount'            => $order->total_price,
            'status'            => 'pending',
        ]);

        // 3. 💌 Сповіщення
        $this->notifier->sendOrderNotification('order_awaiting_payment', $order);

        // 4. Генеруємо посилання на оплату і повертаємо його в об'єкті
        $payUrl = route('payment.wayforpay', ['order' => $order->id]);
        
        return new PaymentResult(isOnline: true, redirectUrl: $payUrl);
    }
}