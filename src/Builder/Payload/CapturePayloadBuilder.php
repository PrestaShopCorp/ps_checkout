<?php

declare(strict_types=1);

namespace PrestaShop\Module\PrestashopCheckout\Builder\Payload;

use PrestaShop\Module\PrestashopCheckout\PayPal\Order\DTO\Write\CaptureOrderPayloadDTO;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\Entity\PayPalOrder;
use PrestaShop\Module\PrestashopCheckout\Repository\PayPalOrderRepository;

class CapturePayloadBuilder
{
    private $payPalOrderRepository;

    public function __construct(PayPalOrderRepository $payPalOrderRepository)
    {
        $this->payPalOrderRepository = $payPalOrderRepository;
    }

    public function buildPayload($mode, $orderId, $merchantId)
    {
        $order = $this->payPalOrderRepository->getPayPalOrderById($orderId);

        return CaptureOrderPayloadDTO::create(
            $mode,
            $orderId,
            ['merchantId' => $merchantId],
            $order->checkCustomerIntent(PayPalOrder::CUSTOMER_INTENT_USES_VAULTING)
        );
    }
}
