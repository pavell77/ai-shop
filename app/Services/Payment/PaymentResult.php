<?php

namespace App\Services\Payment;

class PaymentResult
{
    protected bool $isOnline;
    protected ?string $redirectUrl;

    public function __construct(bool $isOnline, ?string $redirectUrl = null)
    {
        $this->isOnline = $isOnline;
        $this->redirectUrl = $redirectUrl;
    }

    public function isOnline(): bool
    {
        return $this->isOnline;
    }

    public function getRedirectUrl(): ?string
    {
        return $this->redirectUrl;
    }
}