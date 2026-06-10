<?php

namespace App\Services;

use App\Ai\StoreAssistant;
use Illuminate\Support\Facades\Log;
use Exception;
use Laravel\Ai\AiManager;
use Laravel\Ai\Prompts\AgentPrompt;

class AiService
{
    /**
     * Відправка запиту до ШІ через високорівневий метод prompt() провайдера
     */
    public function chat(array $messages): string
    {
        // 1. Беремо останнє повідомлення користувача
        $lastUserText = end($messages)['content'] ?? 'Привіт';

        // 2. Ініціалізуємо нашого помічника в стандартному режимі (для Gemini)
        $assistant = new StoreAssistant(false);

        /** @var AiManager $aiManager */
        $aiManager = app(AiManager::class);

        try {
            // Отримуємо текстовий провайдер Gemini
            $provider = $aiManager->textProvider('gemini');
            $model = config('ai.connections.gemini.model', 'gemini-2.5-flash');
            
            $agentPrompt = new AgentPrompt(
                agent: $assistant, 
                prompt: $lastUserText,
                attachments: [], 
                provider: $provider, 
                model: $model
            );
            
            $response = $provider->prompt($agentPrompt);
            return $response->text;
            
        } catch (Exception $e) {
            Log::warning("Основний ШІ-провайдер [Gemini] недоступний: " . $e->getMessage());

            try {
                // Fallback-перехід на Ollama
                $provider = $aiManager->textProvider('ollama');
                $model = config('ai.providers.ollama.model', 'qwen2.5-coder:7b');
                
                // Створюємо НОВИЙ полегшений об'єкт асистента спеціально для Оллами
                $fallbackAssistant = new StoreAssistant(true);

                $agentPrompt = new AgentPrompt(
                    agent: $fallbackAssistant, // <--- Передаємо легкого агента
                    prompt: $lastUserText,
                    attachments: [],
                    provider: $provider,
                    model: $model
                );
                
                $response = $provider->prompt($agentPrompt);
                return $response->text . "\n\n*(Резервний режим: Локальна Ollama)*";
                
            } catch (Exception $ollamaException) {
                Log::critical("Повний крах ШІ-моделей: " . $ollamaException->getMessage());
                return "Приношу вибачення. Наразі мої ШІ-модулі проходять технічне обслуговування. Зайдіть, будь ласка, трохи пізніше!";
            }
        }
    }
}