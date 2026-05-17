<?php

use App\Models\User;
use App\Models\Category;
use App\Models\Product;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\DeliveryMethod;
use App\Models\PaymentMethod;
use App\Models\NotificationTemplate;
use App\Models\NotificationLog;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('full e-commerce purchase lifecycle works flawlessly', function () {
    // 1. Попередньо засіваємо довідники через Seeder
    $this->seed(\Database\Seeders\DatabaseSeeder::class);

    // 2. Створюємо покупця та товар
    $user = User::factory()->create();
    $category = Category::factory()->create();
    $product = Product::factory()->create([
        'category_id' => $category->id,
        'price' => 500.00
    ]);

    // Додаємо картинку товару (симуляція галереї)
    $product->images()->create([
        'image_path' => 'products/test-item.jpg',
        'is_primary' => true
    ]);

    expect($product->images)->toHaveCount(1);

    // 3. Етап Кошика: Юзер додає 2 одиниці товару в кошик
    $cart = Cart::create(['user_id' => $user->id]);
    $cartItem = CartItem::create([
        'cart_id' => $cart->id,
        'product_id' => $product->id,
        'quantity' => 2
    ]);

    expect($user->cart->items)->toHaveCount(1)
        ->and($user->cart->items->first()->quantity)->toBe(2);

    // 4. Етап Оформлення замовлення (Checkout)
    $delivery = DeliveryMethod::where('code', 'nova_poshta')->first();
    $payment = PaymentMethod::where('code', 'monobank')->first();

    // Розраховуємо фінальну суму: (500.00 * 2) + 80.00 доставка = 1080.00
    $totalPrice = ($product->price * $cartItem->quantity) + $delivery->price;

    $order = Order::create([
        'user_id' => $user->id,
        'delivery_method_id' => $delivery->id,
        'payment_method_id' => $payment->id,
        'total_price' => $totalPrice,
        'status' => 'pending',
        'delivery_status' => 'pending',
        'customer_name' => 'Павло Тестовий',
        'customer_phone' => '+380991112233',
        'delivery_address' => 'Київ, Відділення №1'
    ]);

    // Створюємо зліпок товарів у замовленні
    $orderItem = OrderItem::create([
        'order_id' => $order->id,
        'product_id' => $product->id,
        'price' => $product->price, // Фіксація ціни!
        'quantity' => $cartItem->quantity
    ]);

    // Очищуємо кошик після успішного оформлення
    $cart->delete();

    // Перевіряємо замовлення
    expect($user->orders)->toHaveCount(1)
        ->and($order->total_price)->toEqual(1080.00)
        ->and($orderItem->price)->toEqual(500.00);

    // 5. Етап фіксації фінансів (Створення транзакції банківського шлюзу)
    $transaction = Transaction::create([
        'order_id' => $order->id,
        'payment_method_id' => $payment->id,
        'amount' => $order->total_price,
        'status' => 'pending',
        'gateway_transaction_id' => 'mono_inv_8892aX',
        'payload' => ['currency' => 980, 'holding_period' => 24]
    ]);

    expect($order->transactions)->toHaveCount(1)
        ->and($transaction->payload['currency'])->toBe(980);

    // 6. Етап комунікації (Черга на відправку системного листа)
    $template = NotificationTemplate::where('code', 'order_created')->first();
    
    $log = NotificationLog::create([
        'user_id' => $user->id,
        'template_id' => $template->id,
        'order_id' => $order->id,
        'recipient_email' => $user->email,
        'subject' => str_replace('{order_id}', $order->id, $template->subject),
        'status' => 'pending'
    ]);

    expect($log->subject)->toContain("Ваше замовлення №{$order->id}")
        ->and($log->status)->toBe('pending');
});