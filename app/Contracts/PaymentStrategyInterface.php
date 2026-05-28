<?php

namespace App\Contracts;

use App\Models\Order;
use App\Services\Payment\PaymentResult;

interface PaymentStrategyInterface
{
    /**
     * Обробити специфічний сценарій оплати/доставки
     */
    public function process(Order $order): PaymentResult;
}