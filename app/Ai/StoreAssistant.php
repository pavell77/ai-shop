<?php

namespace App\Ai;

use App\Models\Product;
use Laravel\Ai\AnonymousAgent;

class StoreAssistant extends AnonymousAgent
{
    /**
     * Створюємо власну логіку ініціалізації без обов'язкових зовнішніх параметрів
     */
    public function __construct()
    {
        // 1. Формуємо системні інструкції з базою товарів
        $instructions = $this->buildInstructions();

        // 2. Передаємо їх у батьківський конструктор AnonymousAgent разом із порожніми масивами
        parent::__construct(
            instructions: $instructions,
            messages: [],
            tools: []
        );
    }

    /**
     * Динамічна генерація інструкцій та контексту товарів
     */
    private function buildInstructions(): string
    {
        // Витягуємо товари з бази даних
        $products = Product::select('id', 'name', 'price', 'description')->get()->toArray();
        
        $productsContext = "Перелік доступних товарів у магазині:\n";
        foreach ($products as $product) {
            $productsContext .= "- {$product['name']} (Ціна: {$product['price']} грн): {$product['description']}\n";
        }

        return "Ти — привітний ШІ-консультант інтернет-магазину AI-Shop. 
Ваше завдання — допомагати гостям обирати товари, відповідати на запитання та допомагати з оформленням замовлення.
Спілкуйся виключно українською мовою. Будь ввічливим та лаконічним.

{$productsContext}";
    }

    /**
     * Повертає назву моделі для цього агента
     */
    public function model(): string
    {
        return config('ai.connections.gemini.model', 'gemini-2.5-flash');
    }
}