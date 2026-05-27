<?php

use Livewire\Volt\Component;
use App\Models\DeliveryMethod;
use App\Models\PaymentMethod;
use App\Services\CartService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth; // Імпортуємо фасад Auth

new class extends Component {
    // Дані форми
    public string $name = '';
    public string $email = '';
    public string $phone = '';
    
    public ?int $selectedDeliveryId = null;
    public ?int $selectedPaymentId = null;
    
    // Дані для вибору з БД
    public Collection $deliveryMethods;
    public Collection $paymentMethods;
    
    // Дані кошика
    public array $cartItems = [];
    public float $itemsTotal = 0.00;

    public function mount(CartService $cartService): void
    {
        // 1. Завантажуємо методи доставки та оплати з бази
        $this->deliveryMethods = DeliveryMethod::where('is_active', true)->get();
        $this->paymentMethods = PaymentMethod::where('is_active', true)->get();
        
        // Автоматично обираємо перші методи за замовчуванням
        $this->selectedDeliveryId = $this->deliveryMethods->first()?->id;
        $this->selectedPaymentId = $this->paymentMethods->first()?->id;

        // 2. Використовуємо строгий фасад Auth замість хелперів
        if (Auth::check()) {
            $user = Auth::user();
            $this->name = $user->name;
            $this->email = $user->email;
        }

        // 3. Підтягуємо стан кошика (використовуємо правильний метод getTotalPrice)
        $this->cartItems = $cartService->getItems()->toArray();
        $this->itemsTotal = $cartService->getTotalPrice();
    }

    // Рахуємо фінальну суму (Вартість товарів + обрана доставка)
    public function getTotalProperty(CartService $cartService): float
    {
        $delivery = $this->deliveryMethods->firstWhere('id', $this->selectedDeliveryId);
        $deliveryPrice = $delivery ? (float) $delivery->price : 0.00;
        
        // Тут також викликаємо правильний метод getTotalPrice()
        return $cartService->getTotalPrice() + $deliveryPrice;
    }
    
    // Повертаємо ціну доставки для виведення на фронт
    public function getDeliveryPriceProperty(): float
    {
        $delivery = $this->deliveryMethods->firstWhere('id', $this->selectedDeliveryId);
        return $delivery ? (float) $delivery->price : 0.00;
    }

    // Метод відправки форми та створення замовлення
    public function submitOrder(CartService $cartService): void
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            
            // Строга перевірка: тільки цифри, рівно 12 штук, формат 380XXXXXXXXX
            'phone' => [
                'required',
                'regex:/^380[0-9]{9}$/'
            ],
            
            'selectedDeliveryId' => 'required|exists:delivery_methods,id',
            'selectedPaymentId' => 'required|exists:payment_methods,id',
        ], [
            'name.required' => "Будь ласка, вкажіть ваше ім'я та прізвище.",
            'email.required' => 'Електронна адреса є обов’язковою.',
            'email.email' => 'Введіть коректний email.',
            'phone.required' => 'Номер телефону є обов’язковим.',
            'phone.regex' => 'Некоректний формат номера. Очікується 12 цифр у форматі 380XXXXXXXXX.',
        ]);

        // Якщо код дійшов сюди — дані стовідсотково чисті та валідні!
    }

    // Спрацьовує автоматично, коли в змінну летять вже чисті цифри з фронту
    public function updatedPhone(string $value): void
    {
        // 1. Про всяк випадок ще раз чистимо від усього, крім цифр (якщо прилетів автозаповненням)
        $cleaned = preg_replace('/[^0-9]/', '', $value);

        // 2. Якщо юзер вставив номер, який починається з "80..." (старий формат), перетворюємо на "380"
        if (str_starts_with($cleaned, '80') && strlen($cleaned) === 11) {
            $cleaned = '3' . $cleaned;
        }

        // 3. Якщо юзер почав вводити з нуля (наприклад, "097..."), автопідставляємо "38" попереду
        if (str_starts_with($cleaned, '0') && strlen($cleaned) <= 10) {
            $cleaned = '38' . $cleaned;
        }

        // 4. Жорстке обмеження довжини на рівні PHP (не більше 12 цифр)
        if (strlen($cleaned) > 12) {
            $cleaned = substr($cleaned, 0, 12);
        }

        // Оновлюємо властивість
        $this->phone = $cleaned;
    }

}; ?>

<div class="py-12 text-white">
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
                    <div class="space-y-3">
                        @foreach($deliveryMethods as $method)
                            <label class="flex items-center p-4 border rounded-lg bg-slate-700 border-gray-600 cursor-pointer hover:bg-slate-600/50 transition">
                                <input type="radio" wire:model.live="selectedDeliveryId" value="{{ $method->id }}" class="text-indigo-600 focus:ring-indigo-500 bg-slate-800 border-gray-600">
                                <span class="ml-3 font-medium">{{ $method->name }}</span>
                                <span class="ml-auto font-semibold text-indigo-400">{{ $method->price > 0 ? $method->price . ' ₴' : 'Безкоштовно' }}</span>
                            </label>
                        @endforeach
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

            <div class="bg-slate-800 p-6 rounded-lg shadow h-fit space-y-4">
                <h2 class="text-lg font-medium mb-2">Підсумок замовлення</h2>
                <div class="flex justify-between text-gray-400">
                    <span>Вартість товарів:</span>
                    <span class="font-medium text-white">{{ $itemsTotal }} ₴</span>
                </div>
                <div class="flex justify-between text-gray-400">
                    <span>Доставка:</span>
                    <span class="font-medium text-white">{{ $this->deliveryPrice > 0 ? $this->deliveryPrice . ' ₴' : 'Безкоштовно' }}</span>
                </div>
                <hr class="border-gray-700">
                <div class="flex justify-between text-xl font-bold">
                    <span>До сплати:</span>
                    <span class="text-indigo-400">{{ $this->total }} ₴</span>
                </div>

                <button wire:click="submitOrder" class="w-full mt-4 bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-4 rounded-lg transition shadow-lg shadow-indigo-600/20">
                    Підтвердити замовлення
                </button>
            </div>
        </div>
    </div>
</div>