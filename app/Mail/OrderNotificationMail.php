<?php

namespace App\Mail;

use App\Models\NotificationLog;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Exception;

class OrderNotificationMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public string $subjectText;
    public string $bodyText;
    public int $logId;

    /**
     * Створення нового екземпляра листа
     */
    public function __construct(string $subject, string $body, int $logId)
    {
        $this->subjectText = $subject;
        $this->bodyText = $body;
        $this->logId = $logId;
    }

    /**
     * Визначення конверта (теми) листа
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->subjectText,
        );
    }

    /**
     * Визначення контенту листа (сирий HTML з шаблону БД)
     */
    public function content(): Content
    {
        return new Content(
            htmlString: $this->bodyText,
        );
    }

    /**
     * Викликається автоматично після успішної відправки листа SMTP-сервером
     */
    public function dispatched($mailer): void
    {
        NotificationLog::where('id', $this->logId)->update([
            'status' => 'sent'
        ]);
    }

    /**
     * Викликається автоматично, якщо під час обробки черги чи відправки сталася помилка
     */
    public function failed(Exception $exception): void
    {
        NotificationLog::where('id', $this->logId)->update([
            'status'        => 'failed',
            'error_message' => substr($exception->getMessage(), 0, 500)
        ]);
    }
}