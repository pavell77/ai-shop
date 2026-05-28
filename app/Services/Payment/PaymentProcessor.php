<?php

namespace App\Services\Payment;

use App\Contracts\PaymentStrategyInterface;
use App\Services\Payment\Strategies\CodStrategy;
use App\Services\Payment\Strategies\WayForPayStrategy;
use App\Services\Notification\NotificationService;
use App\Models\PaymentMethod;

class PaymentProcessor
{
    protected NotificationService $notifier;

    public function __construct(NotificationService $notifier)
    {
        $this->notifier = $notifier;
    }

    /**
     * Повернути готову стратегію на основі запису з БД
     */
    public function getStrategy(PaymentMethod $method): PaymentStrategyInterface
    {
        $name = strtolower($method->name);

        if (str_contains($name, 'wayforpay')) {
            return new WayForPayStrategy($this->notifier);
        }

        // За замовчуванням — післяплата (CodStrategy)
        return new CodStrategy($this->notifier);
    }
}