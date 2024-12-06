<?php

declare(strict_types=1);

namespace PrestaShop\Module\PrestashopCheckout\PayPal\Order\DTO\Write;

class CaptureOrderPayloadDTO
{
    private $mode;
    private $orderId;
    private $payee;
    private $vault = false;

    private function __construct($mode, $orderId, $payee, $vault)
    {
        $this->mode = $mode;
        $this->orderId = $orderId;
        $this->payee = $payee;
        $this->vault = $vault;
    }

    public static function create($mode, $orderId, $merchantId, $vault)
    {
        return new self($mode, $orderId, $merchantId, $vault);
    }

    public function toArray(): array
    {
        return [
            'mode' => $this->mode,
            'orderId' => $this->orderId,
            'payee' => $this->payee,
            'vault' => $this->vault,
        ];
    }
}
