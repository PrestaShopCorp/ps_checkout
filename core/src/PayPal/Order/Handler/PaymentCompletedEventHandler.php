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

namespace PsCheckout\Core\PayPal\Order\Handler;

use PsCheckout\Api\ValueObject\PayPalOrderResponse;
use PsCheckout\Core\Order\Action\CreateOrderActionInterface;
use PsCheckout\Core\Order\Action\CreateOrderPaymentActionInterface;
use PsCheckout\Core\OrderState\Action\SetOrderStateActionInterface;
use PsCheckout\Infrastructure\Adapter\Context;
use PsCheckout\Infrastructure\Adapter\ContextInterface;

class PaymentCompletedEventHandler implements EventHandlerInterface
{
    /**
     * @var CreateOrderActionInterface
     */
    private $createOrderAction;

    /**
     * @var CreateOrderPaymentActionInterface
     */
    private $createOrderPaymentAction;

    /**
     * @var SetOrderStateActionInterface
     */
    private $setCompletedOrderStateAction;

    /**
     * @var ContextInterface
     */
    private $context;

    public function __construct(
        CreateOrderActionInterface $createOrderAction,
        CreateOrderPaymentActionInterface $createOrderPaymentAction,
        SetOrderStateActionInterface $setCompletedOrderStateAction,
        ContextInterface $context
    ) {
        $this->createOrderAction = $createOrderAction;
        $this->createOrderPaymentAction = $createOrderPaymentAction;
        $this->setCompletedOrderStateAction = $setCompletedOrderStateAction;
        $this->context = $context;
    }

    /**
     * {@inheritDoc}
     */
    public function handle(PayPalOrderResponse $payPalOrderResponse)
    {
        if ($this->context->getCart()->id) {
            $this->createOrderAction->execute($payPalOrderResponse);
            $this->createOrderPaymentAction->execute($payPalOrderResponse);
        }
        $this->setCompletedOrderStateAction->execute($payPalOrderResponse->getId());
    }
}
