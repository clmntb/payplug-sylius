<?php

declare(strict_types=1);

namespace PayPlug\SyliusPayPlugPlugin\ApiClient;

use Payplug\Resource\Payment;
use Payplug\Resource\Refund;

interface PayPlugApiClientInterface
{
    public const STATUS_CREATED = 'created';
    public const STATUS_CANCELED = 'canceled';
    public const STATUS_CAPTURED = 'captured';
    public const FAILED = 'failed';
    public const REFUNDED = 'refunded';

    public function initialise(
        string $secretKey, 
        ?string $notificationUrlDev = null, 
        ?bool $isOney,
        ?bool $hasFees
    ): void;
    
    public function createPayment(array $data): Payment;

    public function refundPayment(string $paymentId): Refund;

    public function treat($input);

    public function retrieve(string $paymentId): Payment;

    public function getNotificationUrlDev(): ?string;
}
