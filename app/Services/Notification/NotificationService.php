<?php

namespace App\Services\Notification;

use App\Models\Order;
use App\Models\NotificationTemplate;
use App\Models\NotificationLog;
use App\Mail\OrderNotificationMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class NotificationService
{
    /**
     * Відправити динамічний лист по замовленню за кодом шаблону через чергу
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

        // Безпечно визначаємо email отримувача
        $recipientEmail = $order->customer_email 
            ?? $order->email 
            ?? $order->user?->email 
            ?? Auth::user()?->email 
            ?? 'customer@pavell.net';

        // 2. Створюємо запис в історії відправки зі статусом 'pending'
        $log = NotificationLog::create([
            'user_id'         => $order->user_id ?? Auth::id(),
            'template_id'     => $template->id,
            'order_id'        => $order->id,
            'recipient_email' => $recipientEmail,
            'subject'         => $subject,
            'status'          => 'pending',
        ]);

        // 3. Відправляємо Mailable в чергу, передаючи ID логу для збереження точних статусів
        Mail::to($recipientEmail)->queue(new OrderNotificationMail($subject, $body, $log->id));
    }
}