<?php

declare(strict_types=1);

namespace PayPlug\SyliusPayPlugPlugin\ApiClient;

use Payplug\Resource\Payment;
use Payplug\Resource\Refund;
use Payplug\Payplug;

class PayPlugApiClient implements PayPlugApiClientInterface
{
    /** @var string|null */
    private $payplug;
    private $notificationUrlDev;
    private $isOney;
    private $hasFees;

    public function initialise(
        string $secretKey, 
        ?string $notificationUrlDev = null, 
        ?bool $isOney,
        ?bool $hasFees
    ): void
    {
        Payplug::setSecretKey($secretKey);
        $this->payplug = new Payplug($secretKey, '2019-08-06'); 
        $this->notificationUrlDev = $notificationUrlDev;
        $this->isOney = $isOney;
        $this->hasFees = $hasFees;
    }

    public function createPayment(array $data): Payment
    {
        return \Payplug\Payment::create($data, $this->payplug);
    }

    public function refundPayment(string $paymentId): Refund
    {
        return \Payplug\Refund::create($paymentId, $this->payplug);
    }

    public function treat($input)
    {
        return \Payplug\Notification::treat($input, $this->payplug);
    }

    public function retrieve(string $paymentId): Payment
    {
        return \Payplug\Payment::retrieve($paymentId, $this->payplug);
    }

    public function getNotificationUrlDev(): ?string
    {
        return $this->notificationUrlDev;
    }

    public function isOney(): bool
    {
        return $this->isOney;
    }

    public function hasFees(): bool
    {
        return $this->hasFees;
    }
}
