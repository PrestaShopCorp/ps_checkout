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

namespace PsCheckout\Core\Order\Action;

use DateTimeImmutable;
use DateTimeZone;
use PsCheckout\Api\ValueObject\PayPalOrderResponse;
use PsCheckout\Core\Exception\PsCheckoutException;
use PsCheckout\Core\Order\Exception\OrderException;
use PsCheckout\Core\PayPal\Order\Repository\PayPalOrderRepositoryInterface;
use PsCheckout\Infrastructure\Adapter\ConfigurationInterface;
use PsCheckout\Infrastructure\Adapter\CurrencyInterface;
use PsCheckout\Infrastructure\Repository\OrderRepositoryInterface;
use PsCheckout\Presentation\Presenter\FundingSource\FundingSourceTranslationProviderInterface;

class CreateOrderPaymentAction implements CreateOrderPaymentActionInterface
{
    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var PayPalOrderRepositoryInterface
     */
    private $payPalOrderRepository;

    /**
     * @var FundingSourceTranslationProviderInterface
     */
    private $fundingSourceTranslationProvider;

    /**
     * @var ConfigurationInterface
     */
    private $configuration;

    /**
     * @var CurrencyInterface
     */
    private $currency;

    public function __construct(
        OrderRepositoryInterface $orderRepository,
        PayPalOrderRepositoryInterface $payPalOrderRepository,
        FundingSourceTranslationProviderInterface $fundingSourceTranslationProvider,
        ConfigurationInterface $configuration,
        CurrencyInterface $currency
    ) {
        $this->orderRepository = $orderRepository;
        $this->payPalOrderRepository = $payPalOrderRepository;
        $this->fundingSourceTranslationProvider = $fundingSourceTranslationProvider;
        $this->configuration = $configuration;
        $this->currency = $currency;
    }

    /**
     * {@inheritDoc}
     */
    public function execute(PayPalOrderResponse $payPalOrderResponse)
    {
        $payPalOrder = $this->payPalOrderRepository->getOneBy(['id' => $payPalOrderResponse->getId()]);

        $order = $this->orderRepository->getOneBy(['id_cart' => $payPalOrder->getIdCart()]);

        if (!$order) {
            throw new PsCheckoutException('No PrestaShop Order associated to this PayPal Order at this time.');
        }

        $orderPayments = $order->getOrderPaymentCollection();
        $orderPaymentId = null;

        foreach ($orderPayments as $orderPayment) {
            if ($orderPayment->transaction_id === $payPalOrderResponse->getCapture()['id']) {
                $orderPaymentId = (int) $orderPayment->id;
            }
        }

        if ($orderPaymentId) {
            return;
        }

        $currency = $this->currency->getCurrencyInstance($order->id_currency);

        $orderHasInvoice = $order->hasInvoice();
        $orderInvoice = $orderHasInvoice ? $order->getNotPaidInvoicesCollection()->getFirst() : null;

        if ($orderHasInvoice && !$orderInvoice->id) {
            $orderInvoice = null;
        }

        $date = new DateTimeImmutable($payPalOrderResponse->getCreateTime());
        $date->setTimezone(
            new DateTimeZone($this->configuration->get('PS_TIMEZONE') ?? date_default_timezone_get())
        )
            ->format('Y-m-d H:i:s');

        $paymentAdded = $order->addOrderPayment(
            $payPalOrderResponse->getCapture(),
            $this->fundingSourceTranslationProvider->getFundingSourceName($payPalOrderResponse->getFundingSource()),
            $orderPaymentId,
            $currency,
            $date,
            $orderInvoice
        );

        if (!$paymentAdded) {
            throw new OrderException(sprintf('Failed to add a payment to Order #%s.', $payPalOrderResponse->getId()), PsCheckoutException::FAILED_ADD_PAYMENT);
        }
    }
}
