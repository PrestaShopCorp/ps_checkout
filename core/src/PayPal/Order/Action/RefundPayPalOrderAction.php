<?php
/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License version 3.0
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
 */

namespace PsCheckout\Core\PayPal\Order\Action;

use PsCheckout\Api\Http\OrderHttpClientInterface;
use PsCheckout\Core\OrderState\Action\SetOrderStateActionInterface;
use PsCheckout\Core\PayPal\Refund\Exception\PayPalRefundException;
use PsCheckout\Core\PayPal\Refund\ValueObject\PayPalRefund;
use PsCheckout\Core\Settings\Configuration\PayPalConfiguration;
use PsCheckout\Infrastructure\Adapter\ConfigurationInterface;

class RefundPayPalOrderAction implements RefundPayPalOrderActionInterface
{
    /**
     * @var ConfigurationInterface
     */
    private $configuration;

    /**
     * @var OrderHttpClientInterface
     */
    private $orderHttpClient;

    /**
     * @var SetOrderStateActionInterface
     */
    private $setRefundedOrderStateAction;

    /**
     * @param ConfigurationInterface $configuration
     * @param OrderHttpClientInterface $orderHttpClient
     * @param SetOrderStateActionInterface $setRefundedOrderStateAction
     */
    public function __construct(
        ConfigurationInterface $configuration,
        OrderHttpClientInterface $orderHttpClient,
        SetOrderStateActionInterface $setRefundedOrderStateAction
    ) {
        $this->configuration = $configuration;
        $this->orderHttpClient = $orderHttpClient;
        $this->setRefundedOrderStateAction = $setRefundedOrderStateAction;
    }

    /**
     * {@inheritDoc}
     */
    public function execute(PayPalRefund $payPalRefund)
    {
        $payload = [
            'orderId' => $payPalRefund->getPayPalOrderId(),
            'captureId' => $payPalRefund->getCaptureId(),
            'payee' => [
                'merchant_id' => $this->configuration->get(PayPalConfiguration::PS_CHECKOUT_PAYPAL_ID_MERCHANT),
            ],
            'amount' => [
                'currency_code' => $payPalRefund->getCurrencyCode(),
                'value' => $payPalRefund->getAmount(),
            ],
            'note_to_payer' => 'Refund by ' . $this->configuration->get('PS_SHOP_NAME'),
        ];

        $response = $this->orderHttpClient->refundOrder($payload);

        $refund = json_decode($response->getBody(), true);

        if (!$refund['id']) {
            throw new PayPalRefundException('', PayPalRefundException::REFUND_FAILED);
        }

        $this->setRefundedOrderStateAction->execute($payPalRefund->getPayPalOrderId());
    }
}
