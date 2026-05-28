<?php

namespace App\Services\Notification;

use App\Models\Order;
use App\Models\NotificationTemplate;
use App\Models\NotificationLog;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Exception;

class NotificationService
{
    /**
     * Відправити динамічний лист по замовленню за кодом шаблону
     */
    public function sendOrderNotification(string $templateCode, Order $order): void
    {
        $template = NotificationTemplate::where('code', $templateCode)->first();

        if (!$template) {
            Log::error("Notification template with code [{$templateCode}] not found.");
            return;
        }

        // 1. Готуємо дані для заміни шорткодів
        $variables = [
            '{customer_name}' => $order->customer_name,
            '{order_id}'      => $order->id,
            '{total_price}'   => $order->total_price . ' ₴',
            '{address}'       => $order->delivery_address,
        ];

        $subject = str_replace(array_keys($variables), array_values($variables), $template->subject);
        $body    = str_replace(array_keys($variables), array_values($variables), $template->body);

        // Безпечно визначаємо email отримувача: зв'язок моделі -> авторизований юзер -> заглушка
        $recipientEmail = $order->customer_email 
            ?? $order->email 
            ?? $order->user?->email 
            ?? Auth::user()?->email 
            ?? 'customer@pavell.net';

        // 2. Створюємо запис в історії відправки (NotificationLog)
        $log = NotificationLog::create([
            'user_id'         => $order->user_id ?? Auth::id(),
            'template_id'     => $template->id,
            'order_id'        => $order->id,
            'recipient_email' => $recipientEmail,
            'subject'         => $subject,
            'status'          => 'pending',
        ]);

        try {
            // 3. Відправляємо чистий HTML
            Mail::html($body, function ($message) use ($recipientEmail, $subject) {
                $message->to($recipientEmail)
                        ->subject($subject);
            });

            $log->update(['status' => 'sent']);
        } catch (Exception $e) {
            // Фіксуємо детальну помилку в системних логах Laravel, щоб не ламати чекаут користувачу
            Log::error("Mail system error for order #{$order->id}: " . $e->getMessage());
            
            $log->update([
                'status'        => 'failed',
                'error_message' => substr($e->getMessage(), 0, 500)
            ]);
        }
    }
}