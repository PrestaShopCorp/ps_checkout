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

namespace PrestaShop\Module\PrestashopCheckout\PayPal\Payment\Refund\CommandHandler;

use PrestaShop\Module\PrestashopCheckout\Configuration\PrestaShopConfiguration;
use PrestaShop\Module\PrestashopCheckout\Context\PrestaShopContext;
use PrestaShop\Module\PrestashopCheckout\Event\EventDispatcherInterface;
use PrestaShop\Module\PrestashopCheckout\Exception\PayPalException;
use PrestaShop\Module\PrestashopCheckout\Http\MaaslandHttpClient;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\Exception\PayPalOrderException;
use PrestaShop\Module\PrestashopCheckout\PayPal\Payment\Refund\Command\RefundPayPalCaptureCommand;
use PrestaShop\Module\PrestashopCheckout\PayPal\Payment\Refund\Event\PayPalCaptureRefundedEvent;
use PrestaShop\Module\PrestashopCheckout\PayPal\Payment\Refund\Exception\PayPalRefundFailedException;
use PrestaShop\Module\PrestashopCheckout\PayPal\PayPalConfiguration;

class RefundPayPalCaptureCommandHandler
{
    /**
     * @var MaaslandHttpClient
     */
    private $checkoutHttpClient;
    /**
     * @var PayPalConfiguration
     */
    private $payPalConfiguration;
    /**
     * @var PrestaShopConfiguration
     */
    private $prestaShopConfiguration;
    /**
     * @var PrestaShopContext
     */
    private $prestaShopContext;
    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    public function __construct(
        MaaslandHttpClient $checkoutHttpClient,
        PayPalConfiguration $payPalConfiguration,
        PrestaShopConfiguration $prestaShopConfiguration,
        PrestaShopContext $prestaShopContext,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->checkoutHttpClient = $checkoutHttpClient;
        $this->payPalConfiguration = $payPalConfiguration;
        $this->prestaShopConfiguration = $prestaShopConfiguration;
        $this->prestaShopContext = $prestaShopContext;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @param RefundPayPalCaptureCommand $command
     *
     * @throws PayPalException
     * @throws PayPalRefundFailedException
     * @throws PayPalOrderException
     */
    public function handle(RefundPayPalCaptureCommand $command)
    {
        $response = $this->checkoutHttpClient->refundOrder([
            'orderId' => $command->getOrderPayPalId(),
            'captureId' => $command->getCaptureId(),
            'payee' => [
                'merchant_id' => $this->payPalConfiguration->getMerchantId(),
            ],
            'amount' => [
                'currency_code' => $command->getCurrencyCode(),
                'value' => $command->getAmount(),
            ],
            'note_to_payer' => 'Refund by '
                . $this->prestaShopConfiguration->get(
                    'PS_SHOP_NAME',
                    ['id_shop' => $this->prestaShopContext->getShopId()]
                ),
        ]);

        $refund = json_decode($response->getBody(), true);
        $this->eventDispatcher->dispatch(
            new PayPalCaptureRefundedEvent(
                $refund['id'],
                $command->getOrderPayPalId(),
                $refund
            )
        );
    }
}
