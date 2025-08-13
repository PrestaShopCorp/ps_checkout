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

namespace PsCheckout\Core\OrderState\Action;

use PsCheckout\Core\Exception\PsCheckoutException;
use PsCheckout\Core\Order\Validator\OrderAmountValidator;
use PsCheckout\Core\Order\Validator\OrderAmountValidatorInterface;
use PsCheckout\Core\OrderState\Configuration\OrderStateConfiguration;
use PsCheckout\Core\OrderState\Service\OrderStateMapperInterface;
use PsCheckout\Core\PayPal\Order\Provider\PayPalOrderProviderInterface;
use PsCheckout\Core\PayPal\Order\Repository\PayPalOrderRepositoryInterface;
use PsCheckout\Infrastructure\Repository\OrderRepositoryInterface;

class SetCompletedOrderStateAction implements SetOrderStateActionInterface
{
    /**
     * @var PayPalOrderRepositoryInterface
     */
    private $payPalOrderRepository;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var OrderAmountValidatorInterface
     */
    private $orderAmountValidator;

    /**
     * @var OrderStateMapperInterface
     */
    private $orderStateMapper;

    /**
     * @var ChangeOrderStateActionInterface
     */
    private $changeOrderStateAction;

    /**
     * @var PayPalOrderProviderInterface
     */
    private $payPalOrderProvider;

    public function __construct(
        PayPalOrderRepositoryInterface $payPalOrderRepository,
        OrderRepositoryInterface $orderRepository,
        OrderAmountValidatorInterface $orderAmountValidator,
        OrderStateMapperInterface $orderStateMapper,
        ChangeOrderStateActionInterface $changeOrderStateAction,
        PayPalOrderProviderInterface $payPalOrderProvider
    ) {
        $this->payPalOrderRepository = $payPalOrderRepository;
        $this->orderRepository = $orderRepository;
        $this->orderAmountValidator = $orderAmountValidator;
        $this->orderStateMapper = $orderStateMapper;
        $this->changeOrderStateAction = $changeOrderStateAction;
        $this->payPalOrderProvider = $payPalOrderProvider;
    }

    /**
     * {@inheritDoc}
     */
    public function execute(string $payPalOrderId)
    {
        $payPalOrder = $this->payPalOrderRepository->getOneBy(['id' => $payPalOrderId]);

        if (!$payPalOrder) {
            throw new PsCheckoutException('PayPal order not found.', PsCheckoutException::ORDER_NOT_FOUND);
        }

        $payPalOrderResponse = $this->payPalOrderProvider->getById($payPalOrderId);

        $order = $this->orderRepository->getOneBy(['id_cart' => $payPalOrder->getIdCart()]);

        if ($order->hasBeenPaid()) {
            return;
        }

        switch ($this->orderAmountValidator->validate(
            (string) $order->total_paid,
            (string) $payPalOrderResponse->getCapture()['amount']['value']
        )) {
            case OrderAmountValidator::ORDER_FULL_PAID:
            case OrderAmountValidator::ORDER_TO_MUCH_PAID:
                $orderStateKey = OrderStateConfiguration::PS_CHECKOUT_STATE_COMPLETED;

                break;
            case OrderAmountValidator::ORDER_NOT_FULL_PAID:
                $orderStateKey = OrderStateConfiguration::PS_CHECKOUT_STATE_PARTIALLY_PAID;

                break;
        }

        if (!isset($orderStateKey)) {
            throw new PsCheckoutException('Invalid order status key.', PsCheckoutException::PRESTASHOP_ORDER_STATE_ERROR);
        }

        $this->changeOrderStateAction->execute($order->id, $this->orderStateMapper->getIdByKey($orderStateKey));
    }
}
