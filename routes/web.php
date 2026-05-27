<?php

use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;
use App\Http\Controllers\PaymentController; // Створимо контролер трохи пізніше

Route::view('/', 'welcome');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

require __DIR__.'/auth.php';

// Перенаправлення з головної на товари
Route::redirect('/', '/products');

// Роути каталогу товарів
Volt::route('/products', 'products.index')->name('products.index');
Volt::route('/products/{product:slug}', 'products.show')->name('products.show');

// НАШ НОВИЙ РОУТ: Сторінка кошика (доступна всім)
Volt::route('/cart', 'pages.cart')->name('cart');

Volt::route('/checkout', 'pages.checkout')->name('checkout');

// Сторінка успішного замовлення (наприклад, для післяплати)
Route::get('/checkout/success/{order}', function (\App\Models\Order $order) {
    return "<h1>Дякуємо! Замовлення №{$order->id} успішно оформлено!</h1>" .
           "<p>Сума до сплати: {$order->total_price} ₴. Наш менеджер зв'яжеться з вами.</p>";
})->name('checkout.success');

// Проміжний роут для генерації та автонадсилання HTML-форми банку WayForPay
Route::get('/payment/wayforpay/{order}', [PaymentController::class, 'redirectToGateway'])
    ->name('payment.wayforpay');