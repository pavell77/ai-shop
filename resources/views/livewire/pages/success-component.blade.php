<?php

use Livewire\Volt\Component;
use App\Models\Order;

new class extends Component {
    public Order $order;

    public function mount(Order $order)
    {
        // Завантажуємо зв'язки. За макет тепер відповідає x-app-layout в обгортці.
        $this->order = $order->load('paymentMethod');
    }
}; ?>

<div class="max-w-3xl mx-auto my-12 px-4 text-center">
    <div class="inline-flex items-center justify-center w-20 h-20 bg-green-100 text-green-600 rounded-full mb-6">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-12 h-12">
            <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
        </svg>
    </div>

    <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight sm:text-4xl">
        Дякуємо за ваше замовлення!
    </h1>
    <p class="mt-3 text-lg text-gray-600">
        Замовлення <span class="font-bold text-indigo-600">№{{ $order->id }}</span> успешно оформлено.
    </p>

    <div class="mt-10 bg-white border border-gray-200 rounded-2xl shadow-sm overflow-hidden text-left">
        <div class="px-6 py-5 bg-gray-50 border-b border-gray-200">
            <h3 class="text-lg font-semibold leading-6 text-gray-900">Деталі замовлення</h3>
        </div>
        <div class="px-6 py-6 space-y-4">
            <div class="flex justify-between text-sm">
                <span class="text-gray-500">Отримувач:</span>
                <span class="font-medium text-gray-900">{{ $order->customer_name }}</span>
            </div>
            <div class="flex justify-between text-sm">
                <span class="text-gray-500">Телефон:</span>
                <span class="font-medium text-gray-900">{{ $order->customer_phone }}</span>
            </div>
            <div class="flex justify-between text-sm border-t border-gray-100 pt-4">
                <span class="text-gray-500">Спосіб оплати:</span>
                <span class="font-medium text-gray-900">
                    {{ $order->paymentMethod?->name ?? 'Обраний спосіб оплати' }}
                </span>
            </div>
            <div class="flex justify-between text-base font-bold border-t border-gray-200 pt-4 text-gray-900">
                <span>Разом до сплати:</span>
                <span class="text-indigo-600">{{ number_format($order->total_price, 2, '.', '') }} ₴</span>
            </div>
        </div>
    </div>

    <div class="mt-6 p-4 bg-blue-50 rounded-xl text-sm text-blue-700 text-left border border-blue-100">
        @if(in_array($order->status, ['processing', 'paid']))
            <p class="font-semibold text-center">🎉 Оплата пройшла успішно! Ваше замовлення вже готується до відправки.</p>
        @else
            <p class="font-semibold text-center">📞 Замовлення прийнято! Наш менеджер зв'яжеться з вами найближчим часом.</p>
        @endif
    </div>

    <div class="mt-10">
        <a href="{{ url('/') }}" class="inline-flex items-center justify-center px-6 py-3 text-base font-medium rounded-xl text-white bg-indigo-600 hover:bg-indigo-700 transition shadow-sm">
            Повернутися до магазину
        </a>
    </div>
</div>