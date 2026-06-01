<?php

use App\Models\User;
use App\Models\Product;
use App\Models\Category;
use App\Models\Order;
use App\Models\DeliveryMethod;
use App\Models\PaymentMethod;
use App\Services\CartService;
use Livewire\Volt\Volt;
use Illuminate\Support\Str;

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/**
 * --------------------------------------------------------------------------
 * БЛОК 1: ТЕСТИ КОШИКА (Додавання, Оновлення)
 * --------------------------------------------------------------------------
 */

test('анонімний користувач може додати товар до кошика', function () {
    $category = Category::factory()->create();
    $product = Product::factory()->create([
        'category_id' => $category->id,
        'price' => 148.90
    ]);

    // Передаємо продукт у параметрах ініціалізації компонента
    Volt::test('products.card', ['product' => $product])
        ->call('addToCart', $product->id);

    // Перевіряємо стан через CartService
    $cartService = app(CartService::class);
    expect($cartService->getItems())->not->toBeEmpty();
});

test('користувач може змінювати кількість товару в кошику і сума перераховується', function () {
    $category = Category::factory()->create();
    $product = Product::factory()->create(['category_id' => $category->id, 'price' => 148.90]);

    // Спочатку додаємо товар у кошик через сервіс, щоб компонент зміг його завантажити при mount
    $cartService = app(CartService::class);
    $cartService->add($product->id, 1);

    // Тестуємо Volt-компонент кошика з реальними методами increment / decrement
    Volt::test('pages.cart')
        ->assertSet('totalPrice', 148.90)
        ->call('increment', $product->id)
        ->assertSet('totalPrice', 297.80) // 148.90 * 2
        ->call('decrement', $product->id)
        ->assertSet('totalPrice', 148.90);
});

/**
 * --------------------------------------------------------------------------
 * БЛОК 2: МАТРИЦЯ ОФОРМЛЕННЯ ЗАМОВЛЕННЯ (3 доставки x 3 оплати)
 * --------------------------------------------------------------------------
 */

dataset('checkout_combinations', [
    // Нова Пошта
    ['Нова Пошта', 'WayForPay', true],  // Назва доставки, назва оплати, чи це онлайн-редирект
    ['Нова Пошта', 'LiqPay', true],
    ['Нова Пошта', 'Готівка', false],

    // Самовивіз
    ['Самовивіз', 'WayForPay', true],
    ['Самовивіз', 'LiqPay', true],
    ['Самовивіз', 'Готівка', false],

    // Кур'єр
    ['Кур’єр', 'WayForPay', true],
    ['Кур’єр', 'LiqPay', true],
    ['Кур’єр', 'Готівка', false],
]);

test('оформлення замовлення з різними типами доставки та оплати', function (string $deliveryName, string $paymentName, bool $isOnlinePayment) {
    // 1. Готуємо необхідні довідники в БД, перевизначаючи поля фабрики під датасет
    $deliveryMethod = DeliveryMethod::factory()->create([
        'name' => $deliveryName,
        'code' => Str::slug($deliveryName, '_'),
        'price' => str_contains(mb_strtolower($deliveryName), 'самовивіз') ? 0.00 : 80.00
    ]);

    $paymentMethod = PaymentMethod::factory()->create([
        'name' => $paymentName,
        'code' => Str::slug($paymentName, '_')
    ]);

    // 2. Створюємо товар та наповнюємо кошик через сервіс
    $category = Category::factory()->create();
    $product = Product::factory()->create(['category_id' => $category->id, 'price' => 148.90]);
    
    $cartService = app(CartService::class);
    $cartService->add($product->id, 1);

    // 3. Тестуємо Volt-компонент сторінки checkout
    $component = Volt::test('pages.checkout')
        ->set('name', 'Павло')
        ->set('phone', '380670000012')
        ->set('email', 'pavell@pavell.net')
        ->set('selectedDeliveryId', $deliveryMethod->id)
        ->set('selectedPaymentId', $paymentMethod->id);

    // Динамічно заповнюємо адресу
    if (str_contains(mb_strtolower($deliveryName), 'самовивіз')) {
        $component->set('deliveryAddress', 'Самовивіз з головного офісу Precinct 13');
    } else {
        $component->set('deliveryAddress', 'м. Київ, Відділення №15');
    }

    // 4. Відправляємо замовлення
    $component->call('submitOrder');

    // 5. Перевіряємо збереження в базі даних з урахуванням логіки статусів твого додатку
    $expectedStatus = ($paymentName === 'WayForPay') ? 'pending_payment' : 'pending';

    $this->assertDatabaseHas('orders', [
        'customer_name' => 'Павло',
        'customer_phone' => '380670000012',
        'delivery_method_id' => $deliveryMethod->id,
        'payment_method_id' => $paymentMethod->id,
        'status' => $expectedStatus
    ]);

    $order = Order::latest()->first();

    // 6. Перевіряємо редиректи залежно від стратегії оплати
    if ($isOnlinePayment) {
        $component->assertRedirect(); 
    } else {
        // Для офлайн-оплати (готівка) — прямий перехід на success
        $component->assertRedirect(route('checkout.success', ['order' => $order->id]));
    }

    // 7. Перевіряємо, що кошик став порожнім через сервіс
    expect($cartService->getItems())->toBeEmpty();

})->with('checkout_combinations');