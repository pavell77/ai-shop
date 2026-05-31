<?php

use Livewire\Volt\Component;
use App\Models\DeliveryMethod;
use App\Models\PaymentMethod;
use App\Models\Order;
use App\Models\OrderItem;
use App\Services\CartService;
use App\Services\Notification\NotificationService;
use App\Services\Payment\PaymentProcessor;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

new class extends Component {
    public string $name = '';
    public string $phone = '';
    public string $email = '';
    public string $deliveryAddress = '';

    public ?int $selectedDeliveryId = null;
    public ?int $selectedPaymentId = null;

    public Collection $deliveryMethods;
    public Collection $paymentMethods;

    public float $itemsTotal = 0.00;

    public function mount(CartService $cartService): void
    {
        $this->deliveryMethods = DeliveryMethod::all();
        $this->paymentMethods = PaymentMethod::all();
        
        $this->selectedDeliveryId = $this->deliveryMethods->first()?->id;
        $this->selectedPaymentId = $this->paymentMethods->first()?->id;
        
        $this->itemsTotal = $cartService->getTotalPrice();

        if (Auth::check()) {
            $user = Auth::user();
            $this->name = $user->name ?? '';
            $this->email = $user->email ?? '';
        }
    }

    public function updatedPhone(string $value): void
    {
        $cleaned = preg_replace('/[^0-9]/', '', $value);
        if (str_starts_with($cleaned, '80') && strlen($cleaned) === 11) {
            $cleaned = '3' . $cleaned;
        }
        if (str_starts_with($cleaned, '0') && strlen($cleaned) <= 10) {
            $cleaned = '38' . $cleaned;
        }
        if (strlen($cleaned) > 12) {
            $cleaned = substr($cleaned, 0, 12);
        }
        $this->phone = $cleaned;
    }

    public function updatedSelectedDeliveryId($value): void
    {
        $method = $this->deliveryMethods->firstWhere('id', $value);
        if ($method && str_contains(strtolower($method->name), 'самовивіз')) {
            $this->deliveryAddress = 'Самовивіз з головного офісу Precinct 13';
        } else {
            $this->deliveryAddress = '';
        }
    }

    public function getDeliveryPriceProperty(): float
    {
        $delivery = $this->deliveryMethods->firstWhere('id', $this->selectedDeliveryId);
        return $delivery ? (float) $delivery->price : 0.00;
    }

    public function getTotalProperty(): float
    {
        return $this->itemsTotal + $this->deliveryPrice;
    }

    public function submitOrder(CartService $cartService, NotificationService $notifier)
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => ['required', 'regex:/^380[0-9]{9}$/'],
            'deliveryAddress' => 'required|string|max:500',
            'selectedDeliveryId' => 'required|exists:delivery_methods,id',
            'selectedPaymentId' => 'required|exists:payment_methods,id',
        ], [
            'name.required' => "Будь ласка, вкажіть ваше ім'я та прізвище.",
            'email.required' => 'Електронна адреса є обов’язковою.',
            'email.email' => 'Введіть коректний email.',
            'phone.required' => 'Номер телефону є обов’язковим.',
            'phone.regex' => 'Формат номера телефону має бути 380XXXXXXXXX.',
            'deliveryAddress.required' => 'Будь ласка, вкажіть адресу або відділення доставки.',
        ]);

        $cartItems = $cartService->getItems();
        
        if ($cartItems->isEmpty()) {
            session()->flash('error', 'Ваш кошик порожній.');
            return;
        }

        $order = DB::transaction(function () use ($cartItems) {
            $order = Order::create([
                'user_id' => Auth::id() ?? null,
                'delivery_method_id' => $this->selectedDeliveryId,
                'payment_method_id' => $this->selectedPaymentId,
                'total_price' => $this->total,
                'status' => 'pending',
                'delivery_status' => 'pending',
                'customer_name' => $this->name,
                'customer_phone' => $this->phone,
                'delivery_address' => $this->deliveryAddress,
            ]);

            foreach ($cartItems as $item) {
                OrderItem::create([
                    'order_id'   => $order->id,
                    'product_id' => $item->product->id,    
                    'price'      => $item->product->price, 
                    'quantity'   => $item->quantity,       
                ]);
            }

            return $order;
        });

        $cartService->clear();

        $paymentMethod = $this->paymentMethods->firstWhere('id', $this->selectedPaymentId);
        
        $processor = new PaymentProcessor($notifier);
        $strategy  = $processor->getStrategy($paymentMethod);

        $result = $strategy->process($order);

        if ($result->isOnline()) {
            return redirect()->to($result->getRedirectUrl());
        }

        return redirect()->route('checkout.success', ['order' => $order->id]);
    }
}; ?><div class="py-12 text-white">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <h1 class="text-2xl font-semibold mb-6">Оформлення замовлення</h1>
        
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 space-y-6">
                <div class="bg-slate-800 p-6 rounded-lg shadow">
                    <h2 class="text-lg font-medium mb-4">1. Дані отримувача</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-400">Ім'я та Прізвище</label>
                            <input type="text" wire:model="name" 
                                class="mt-1 block w-full rounded-md bg-slate-700 text-white focus:ring-indigo-500 focus:border-indigo-500 @error('name') border-red-500 text-red-200 @else border-gray-600 @enderror">
                            @error('name') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-400">Телефон</label>
                            <input type="text" 
                                wire:model="phone" 
                                placeholder="380XXXXXXXXX" 
                                maxlength="12"
                                x-on:input="$el.value = $el.value.replace(/[^0-9]/g, '')"
                                class="mt-1 block w-full rounded-md bg-slate-700 text-white focus:ring-indigo-500 focus:border-indigo-500 @error('phone') border-red-500 text-red-200 @else border-gray-600 @enderror">
                            @error('phone') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-400">Email</label>
                            <input type="email" wire:model="email" 
                                class="mt-1 block w-full rounded-md bg-slate-700 text-white focus:ring-indigo-500 focus:border-indigo-500 @error('email') border-red-500 text-red-200 @else border-gray-600 @enderror">
                            @error('email') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>

                <div class="bg-slate-800 p-6 rounded-lg shadow">
                    <h2 class="text-lg font-medium mb-4">2. Спосіб доставки</h2>
                    <div class="space-y-3 mb-4">
                        @foreach($deliveryMethods as $method)
                            <label class="flex items-center p-4 border rounded-lg bg-slate-700 border-gray-600 cursor-pointer hover:bg-slate-600/50 transition">
                                <input type="radio" wire:model.live="selectedDeliveryId" value="{{ $method->id }}" class="text-indigo-600 focus:ring-indigo-500 bg-slate-800 border-gray-600">
                                <span class="ml-3 font-medium">{{ $method->name }}</span>
                                <span class="ml-auto font-semibold text-indigo-400">{{ $method->price > 0 ? $method->price . ' ₴' : 'Безкоштовно' }}</span>
                            </label>
                        @endforeach
                    </div>

                    <div class="mt-4 border-t border-gray-700 pt-4">
                        <label class="block text-sm font-medium text-gray-400 mb-1">Адреса доставки / Номер відділення Нової Пошти</label>
                        <input type="text" 
                               wire:model="deliveryAddress" 
                               placeholder="Наприклад: м. Київ, Відділення №15 або вул. Хрещатик 1, кв. 13"
                               {{ str_contains(strtolower($deliveryMethods->firstWhere('id', $selectedDeliveryId)?->name ?? ''), 'самовивіз') ? 'disabled' : '' }}
                               class="block w-full rounded-md bg-slate-700 text-white focus:ring-indigo-500 focus:border-indigo-500 @error('deliveryAddress') border-red-500 text-red-200 @else border-gray-600 @enderror">
                        @error('deliveryAddress') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="bg-slate-800 p-6 rounded-lg shadow">
                    <h2 class="text-lg font-medium mb-4">3. Спосіб оплати</h2>
                    <div class="space-y-3">
                        @foreach($paymentMethods as $method)
                            <label class="flex items-center p-4 border rounded-lg bg-slate-700 border-gray-600 cursor-pointer hover:bg-slate-600/50 transition">
                                <input type="radio" wire:model.live="selectedPaymentId" value="{{ $method->id }}" class="text-indigo-600 focus:ring-indigo-500 bg-slate-800 border-gray-600">
                                <span class="ml-3 font-medium">{{ $method->name }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="bg-slate-800 p-6 rounded-lg shadow h-fit flex flex-col items-center space-y-4">
                <h2 class="text-lg font-medium mb-2 w-full text-left">Підсумок замовлення</h2>
                <div class="flex justify-between text-gray-400 w-full">
                    <span>Вартість товарів:</span>
                    <span class="font-medium text-white">{{ $itemsTotal }} ₴</span>
                </div>
                <div class="flex justify-between text-gray-400 w-full">
                    <span>Доставка:</span>
                    <span class="font-medium text-white">{{ $this->deliveryPrice > 0 ? $this->deliveryPrice . ' ₴' : 'Безкоштовно' }}</span>
                </div>
                <hr class="border-gray-700 w-full">
                <div class="flex justify-between text-xl font-bold w-full">
                    <span>До сплати:</span>
                    <span class="text-indigo-400">{{ $this->total }} ₴</span>
                </div>

                <button wire:click="submitOrder" class="w-full mt-4 bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-4 rounded-lg transition shadow-lg shadow-indigo-600/20">
                    Підтвердити замовлення
                </button>

                {{-- Наша нова кнопка повернення до покупок на сторінці оформлення --}}
                <div class="mt-4 text-center">
                    <a href="{{ route('products.index') }}" 
                       wire:navigate 
                       class="inline-flex items-center justify-center gap-1.5 text-sm font-medium text-indigo-400 hover:text-indigo-300 transition group">
                        <svg class="h-4 w-4 transform group-hover:-translate-x-0.5 transition duration-200" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                        </svg>
                        Повернутися до покупок
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>