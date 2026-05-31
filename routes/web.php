<?php

use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;
use App\Http\Controllers\PaymentController;

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

// Сторінка кошика (доступна всім)
Volt::route('/cart', 'pages.cart')->name('cart');

// Сторінка оформлення замовлення
Volt::route('/checkout', 'pages.checkout')->name('checkout');

/*
|--------------------------------------------------------------------------
| ПЛАТІЖНА ІНТЕГРАЦІЯ WAYFORPAY
|--------------------------------------------------------------------------
*/

// 1. Сюди переходить юзер після натискання кнопки чекауту (Генерація форми та підпису)
Route::get('/payment/wayforpay/{order}', [PaymentController::class, 'redirectToGateway'])
    ->name('payment.wayforpay');

// 2. Сюди стукають сервери банку в фоні, коли оплата пройшла (Наш Webhook-колбек)
Route::post('/payment/wayforpay/callback', [PaymentController::class, 'callback'])
    ->name('payment.wayforpay.callback');

// 3. Сюди повертається сам клієнт кнопкою зі сторінки банку (Наша сторінка подяки)
// Використовуємо Route::any, бо банк іноді повертає користувача через POST-запит
Route::any('/checkout/success/{order}', [PaymentController::class, 'success'])
    ->name('checkout.success');