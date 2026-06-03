<?php

namespace App\Services\Payment\Strategies;

use App\Contracts\PaymentStrategyInterface;
use App\Models\Order;
use App\Models\Transaction;
use App\Services\Notification\NotificationService;
use App\Services\Payment\PaymentResult;

class CodStrategy implements PaymentStrategyInterface
{
    protected NotificationService $notifier;

    public function __construct(NotificationService $notifier)
    {
        $this->notifier = $notifier;
    }

    public function process(Order $order): PaymentResult
    {
        // 1. Фіксуємо транзакцію в БД
        Transaction::create([
            'order_id'          => $order->id,
            'payment_method_id' => $order->payment_method_id,
            'amount'            => $order->total_price,
            'status'            => 'pending',
        ]);

        // 2. 💌 Надсилаємо лист
        $this->notifier->sendOrderNotification('order_created_cod', $order);

        // 3. Просто повертаємо намір, що це офлайн-сценарій
        return new PaymentResult(isOnline: false);
    }
}