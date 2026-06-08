<?php

namespace App\Ai;

use App\Models\Product;
use Laravel\Ai\AnonymousAgent;

class StoreAssistant extends AnonymousAgent
{
    // Зберігаємо стан, щоб метод model() знав, де ми знаходимося
    protected bool $isFallback;

    /**
     * Створюємо власну логіку ініціалізації з підтримкою легкого резервного режиму
     */
    public function __construct(bool $isFallback = false)
    {
        $this->isFallback = $isFallback;

        // 1. Формуємо системні інструкції (враховуючи режим)
        $instructions = $this->buildInstructions($isFallback);

        // 2. Передаємо їх у батьківський конструктор
        parent::__construct(
            instructions: $instructions,
            messages: [],
            tools: []
        );
    }

    /**
     * Динамічна генерація інструкцій та контексту товарів
     */
    private function buildInstructions(bool $isFallback): string
    {
        // Якщо увімкнено резервний режим (для локальної Оллами)
        if ($isFallback) {
            return "Ти — привітний локальний резервний ШІ-консультант інтернет-магазину AI-Shop. 
Наразі зв'язок з основною базою даних товарів обмежений через технічні причини. 
Твоє завдання — ввічливо привітатися, відповісти на загальні питання користувача українською мовою та підказати, що актуальний асортимент і ціни краще подивитися безпосередньо в каталозі сайту. 
Будь лаконічним і відповідай коротко.";
        }

        // --- ОСНОВНИЙ РЕЖИМ ДЛЯ GEMINI (З БАЗОЮ ТОВАРІВ) ---
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
     * Повертає назву моделі для цього агента динамічно
     */
    public function model(): string
    {
        // Якщо ми у фолбеку — повертаємо Квен з налаштувань Ollama, інакше — Gemini
        if ($this->isFallback) {
            return config('ai.providers.ollama.model', 'qwen2.5-coder:7b');
        }

        return config('ai.connections.gemini.model', 'gemini-2.5-flash');
    }
}