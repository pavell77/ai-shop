<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\Product;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class CartService
{
    /**
     * Отримуємо або створюємо кошик для поточного сеансу (користувач або гість).
     */
    protected function getCart(): Cart
    {
        // 1. Якщо користувач авторизований, прив'язуємо кошик до його ID
        if (Auth::check()) {
            return Cart::firstOrCreate(
                ['user_id' => Auth::id()]
            );
        }

        // 2. Якщо це гість, прив'язуємо до унікального ID сесії браузера
        return Cart::firstOrCreate(
            ['session_id' => Session::getId()]
        );
    }

    /**
     * Отримати всі товари в кошику з моделями продуктів.
     */
    public function getItems(): Collection
    {
        $cart = $this->getCart();

        // Завантажуємо зв'язок 'items' разом із продуктами (жадібне завантаження / eager loading)
        return $cart->items()->with('product')->get()->map(function ($item) {
            return (object) [
                'id' => $item->id,
                'product' => $item->product,
                'quantity' => $item->quantity,
                'subtotal' => $item->product->price * $item->quantity,
            ];
        });
    }

    /**
     * Додати товар до кошика.
     */
    public function add(int $productId, int $quantity = 1): void
    {
        $cart = $this->getCart();

        // Шукаємо, чи є вже такий товар у цьому кошику
        $item = $cart->items()->where('product_id', $productId)->first();

        if ($item) {
            // Якщо є — просто збільшуємо кількість
            $item->increment('quantity', $quantity);
        } else {
            // ВИПРАВЛЕНО: замість "compression:" тепер стандартний "else"
            // Якщо немає — створюємо новий запис
            $cart->items()->create([
                'product_id' => $productId,
                'quantity' => $quantity,
            ]);
        }
    }

    /**
     * Оновити кількість товару вручну (наприклад, з інпуту в кошику).
     */
    public function updateQuantity(int $productId, int $quantity): void
    {
        $cart = $this->getCart();
        $item = $cart->items()->where('product_id', $productId)->first();

        if (!$item) {
            return;
        }

        if ($quantity <= 0) {
            $item->delete();
            return;
        }

        $item->update(['quantity' => $quantity]);
    }

    /**
     * Видалити конкретний товар з кошика.
     */
    public function remove(int $productId): void
    {
        $cart = $this->getCart();
        $cart->items()->where('product_id', $productId)->delete();
    }

    /**
     * Повністю очистити кошик (наприклад, після успішного оформлення замовлення).
     */
    public function clear(): void
    {
        $this->getCart()->items()->delete();
    }

    /**
     * Отримати загальну кількість штук товарів (для червоного баджа в шапці).
     */
    public function getTotalCount(): int
    {
        return (int) $this->getCart()->items()->sum('quantity');
    }

    /**
     * Отримати загальну вартість всього кошика.
     */
    public function getTotalPrice(): float
    {
        return (float) $this->getItems()->sum('subtotal');
    }
}