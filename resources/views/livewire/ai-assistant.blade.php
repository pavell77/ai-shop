<?php

use function Livewire\Volt\{state, mount};
use Illuminate\Support\Facades\Auth;
use App\Services\AiService;

state([
    'isOpen' => false,
    'messages' => [],
    'input' => '',
    'isLoading' => false
]);

mount(function () {
    $this->messages = session()->get('ai_chat_history', []);

    if (empty($this->messages)) {
        $name = Auth::check() ? Auth::user()->name : 'гостю';
        $this->messages[] = [
            'role' => 'assistant',
            'content' => "Привіт, {$name}! 🤖 Я твій ШІ-помічник магазину AI-Shop.\nЯ можу підібрати товар або допомогти з оформленням. Що тебе цікавить?"
        ];
        session()->put('ai_chat_history', $this->messages);
    }
});

$sendMessage = function (AiService $aiService) {
    if (trim($this->input) === '') return;

    // 1. Додаємо репліку користувача на екран
    $this->messages[] = [
        'role' => 'user',
        'content' => $this->input
    ];

    $this->isLoading = true;
    
    // Очищаємо поле введення, зберігаємо історію в сесії
    $currentMessages = $this->messages;
    $this->input = '';
    session()->put('ai_chat_history', $currentMessages);

    // 2. Робимо реальний запит до ШІ через наш відмовостійкий сервіс
    // Ми передаємо всю історію повідомлень, щоб ШІ тримав нитку розмови
    $aiResponse = $aiService->chat($currentMessages);

    // 3. Додаємо відповідь ШІ в чат
    $this->messages[] = [
        'role' => 'assistant',
        'content' => $aiResponse
    ];

    session()->put('ai_chat_history', $this->messages);
    $this->isLoading = false;
};

?>

<div x-data="{ open: @entangle('isOpen') }" class="fixed bottom-5 right-5 z-50 font-sans">
    <button @click="open = !open" 
            class="bg-indigo-600 hover:bg-indigo-700 text-white rounded-full p-4 shadow-xl transition-all duration-300 transform hover:scale-110 flex items-center justify-center focus:outline-none">
        <svg x-show="!open" xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
        </svg>
        <svg x-show="open" xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="display: none;">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
        </svg>
    </button>

    <div x-show="open" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 translate-y-10 scale-95"
         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 translate-y-0 scale-100"
         x-transition:leave-end="opacity-0 translate-y-10 scale-95"
         class="absolute bottom-20 right-0 w-96 h-[500px] bg-white dark:bg-gray-800 rounded-2xl shadow-2xl border border-gray-200 dark:border-gray-700 flex flex-col overflow-hidden"
         style="display: none;">
        
        <div class="bg-indigo-600 p-4 text-white flex items-center justify-between shadow-md">
            <div class="flex items-center space-x-3">
                <div class="w-2.5 h-2.5 bg-green-400 rounded-full animate-pulse"></div>
                <div>
                    <h3 class="font-semibold text-sm">AI Консультант</h3>
                    <p class="text-xs text-indigo-200">Онлайн | На базі Laravel AI</p>
                </div>
            </div>
            <button @click="open = false" class="text-indigo-200 hover:text-white transition">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                </svg>
            </button>
        </div>

        <div x-init="$watch('messages', () => { $el.scrollTop = $el.scrollHeight })" 
             class="flex-1 p-4 overflow-y-auto space-y-4 bg-gray-50 dark:bg-gray-900 custom-scrollbar">
            
            @foreach($messages as $message)
                <div class="flex {{ $message['role'] === 'user' ? 'justify-end' : 'justify-start' }}">
                    <div class="max-w-[85%] rounded-2xl px-4 py-2.5 text-sm shadow-sm 
                        {{ $message['role'] === 'user' 
                            ? 'bg-indigo-600 text-white rounded-br-none' 
                            : 'bg-white dark:bg-gray-800 text-gray-800 dark:text-gray-200 border border-gray-200 dark:border-gray-700 rounded-bl-none' }}">
                        <p class="whitespace-pre-line">{{ $message['content'] }}</p>
                    </div>
                </div>
            @endforeach

            @if($isLoading)
                <div class="flex justify-start">
                    <div class="bg-white dark:bg-gray-800 text-gray-500 border border-gray-200 dark:border-gray-700 rounded-2xl rounded-bl-none px-4 py-3 shadow-sm flex items-center space-x-1">
                        <div class="w-2 h-2 bg-indigo-500 rounded-full animate-bounce" style="animation-delay: 0ms"></div>
                        <div class="w-2 h-2 bg-indigo-500 rounded-full animate-bounce" style="animation-delay: 150ms"></div>
                        <div class="w-2 h-2 bg-indigo-500 rounded-full animate-bounce" style="animation-delay: 300ms"></div>
                    </div>
                </div>
            @endif
        </div>

        <form wire:submit.prevent="sendMessage" class="p-3 bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700 flex items-center space-x-2">
            <input type="text" 
                   wire:model="input"
                   placeholder="Запитайте щось або знайдіть товар..." 
                   class="flex-1 bg-gray-100 dark:bg-gray-900 text-sm text-gray-800 dark:text-gray-200 rounded-xl px-4 py-2.5 border-none focus:ring-2 focus:ring-indigo-500 placeholder-gray-400 dark:placeholder-gray-500"
                   {{ $isLoading ? 'disabled' : '' }}>
            
            <button type="submit" 
                    {{ $isLoading ? 'disabled' : '' }}
                    class="bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 text-white rounded-xl p-2.5 transition flex items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 transform rotate-90" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9-2-9-18-9 18 9-2zm0 0v-8" />
                </svg>
            </button>
        </form>
    </div>
</div>