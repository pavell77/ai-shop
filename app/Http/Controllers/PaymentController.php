<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    
    /**
     * Крок А: Генерація форми за канонічним алгоритмом SDK WayForPay
     */
    public function redirectToGateway(Order $order)
    {
        // Дозволяємо перехід, якщо замовлення pending або pending_payment
        if (!in_array($order->status, ['pending', 'pending_payment'])) {
            return redirect()->url('/')->with('error', 'Це замовлення не очікує на оплату.');
        }

        $merchantAccount = config('services.wayforpay.account');
        $secretKey = config('services.wayforpay.secret');
        
        $merchantDomainName = request()->getHost();
        if ($merchantDomainName === '127.0.0.1') {
            $merchantDomainName = 'localhost';
        }

        // Генеруємо референс для банку
        $orderReference = $order->id . '_' . time(); 
        $orderDate = $order->created_at->timestamp;
        $amount = number_format($order->total_price, 2, '.', '');
        $currency = 'UAH';

        $productNames = [];
        $productPrices = [];
        $productCounts = [];

        // ПРАВИЛЬНО: Завантажуємо зв'язок items та вкладений product з твоїх моделей
        $order->load('items.product');

        if ($order->items && $order->items->count() > 0) {
            foreach ($order->items as $item) {
                // Очищаємо назву від символів, які можуть порушити підпис
                $name = $item->product->name ?? 'Товар';
                $productNames[] = str_replace(['"', "'", ';'], '', $name);
                $productPrices[] = number_format($item->price, 2, '.', '');
                $productCounts[] = (int)$item->quantity;
            }
        } else {
            $productNames[] = "Замовлення №" . $order->id;
            $productPrices[] = $amount;
            $productCounts[] = 1;
        }

        // БАЗОВІ ПАРАМЕТРИ ДЛЯ СІГНАТУРИ
        $signatureParams = [
            $merchantAccount,
            $merchantDomainName,
            $orderReference,
            $orderDate,
            $amount,
            $currency
        ];

        // АЛГОРИТМ SDK: Послідовно пушимо масиви (ВСІ назви -> ВСІ кількості -> ВСІ ціни)
        foreach ($productNames as $name) {
            $signatureParams[] = $name;
        }
        foreach ($productCounts as $count) {
            $signatureParams[] = $count;
        }
        foreach ($productPrices as $price) {
            $signatureParams[] = $price;
        }

        // Склеюємо через крапку з комою
        $stringToSign = implode(';', $signatureParams);
        $merchantSignature = hash_hmac('md5', $stringToSign, $secretKey);

        // Оновлюємо унікальний ідентифікатор у транзакції
        $transaction = \App\Models\Transaction::where('order_id', $order->id)->latest()->first();
        if ($transaction) {
            $transaction->update([
                'gateway_transaction_id' => $orderReference,
            ]);
        }

        return view('payment.wayforpay_redirect', [
            'gatewayUrl' => config('services.wayforpay.url'),
            'fields' => [
                'merchantAccount' => $merchantAccount,
                'merchantAuthType' => 'SimpleSignature',
                'merchantDomainName' => $merchantDomainName,
                'orderReference' => $orderReference,
                'orderDate' => $orderDate,
                'amount' => $amount,
                'currency' => $currency,
                'productName' => $productNames,   
                'productPrice' => $productPrices, 
                'productCount' => $productCounts, 
                'merchantSignature' => $merchantSignature,
                'returnUrl' => route('checkout.success', ['order' => $order->id]),
                'serviceUrl' => route('payment.wayforpay.callback'),
                'clientFirstName' => $order->customer_name ?? 'Гість',
                'clientPhone' => $order->customer_phone ?? '380000000000',
            ]
        ]);
    }
    
    /**
     * Крок Б: Обробка Webhook Callback від серверів WayForPay
     */
    public function callback(Request $request)
    {
        Log::info('WayForPay Callback Received:', $request->all());
        
        // До цього кроку ми повернемося відразу після тесту форми редиректу
        return response()->json(['status' => 'accept']);
    }

    /**
     * Крок В: Точка повернення користувача після банку
     */
    public function success(Order $order)
    {
        if (request()->isMethod('post')) {
            Log::info("User returned to success endpoint via POST for order #{$order->id}", request()->all());
            return redirect()->route('checkout.success', ['order' => $order->id]);
        }

        // Просто повертаємо представлення, компонент сам розбереться з макетом
        return view('livewire.pages.success', compact('order'));
    }
}