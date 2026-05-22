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

use PsCheckout\Api\ValueObject\PayPalOrderResponse;
use PsCheckout\Core\Exception\PsCheckoutException;
use PsCheckout\Core\PayPal\Order\Repository\PayPalOrderMatrixRepositoryInterface;
use PsCheckout\Core\PayPal\Order\Repository\PayPalOrderRepositoryInterface;
use PsCheckout\Infrastructure\Adapter\ContextInterface;
use PsCheckout\Infrastructure\Repository\OrderRepositoryInterface;

class CreateOrderAction implements CreateOrderActionInterface
{
    /**
     * @var ContextInterface
     */
    private $context;

    /**
     * @var CreateValidateOrderDataActionInterface
     */
    private $createValidateOrderDataAction;

    /**
     * @var ValidateOrderActionInterface
     */
    private $validateOrderAction;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var PayPalOrderMatrixRepositoryInterface
     */
    private $orderMatrixRepository;

    /**
     * @var PayPalOrderRepositoryInterface
     */
    private $payPalOrderRepository;

    public function __construct(
        ContextInterface $context,
        CreateValidateOrderDataActionInterface $createValidateOrderDataAction,
        ValidateOrderActionInterface $validateOrderAction,
        OrderRepositoryInterface $orderRepository,
        PayPalOrderMatrixRepositoryInterface $orderMatrixRepository,
        PayPalOrderRepositoryInterface $payPalOrderRepository
    ) {
        $this->context = $context;
        $this->createValidateOrderDataAction = $createValidateOrderDataAction;
        $this->validateOrderAction = $validateOrderAction;
        $this->orderRepository = $orderRepository;
        $this->orderMatrixRepository = $orderMatrixRepository;
        $this->payPalOrderRepository = $payPalOrderRepository;
    }

    /**
     * {@inheritDoc}
     */
    public function execute(PayPalOrderResponse $payPalOrder)
    {
        $localPayPalOrder = $this->payPalOrderRepository->getOneBy(['id' => $payPalOrder->getId()]);
        if ($localPayPalOrder !== null) {
            $expectedCartId = (int) $localPayPalOrder->getIdCart();
            $contextCart = $this->context->getCart();
            if ($expectedCartId > 0 && (!$contextCart || (int) $contextCart->id !== $expectedCartId)) {
                $cart = new \Cart($expectedCartId);
                if (\Validate::isLoadedObject($cart)) {
                    $this->context->setCurrentCart($cart);
                    if ($cart->id_customer) {
                        $this->context->updateCustomer(new \Customer($cart->id_customer));
                    }
                }
            }
        }

        $cartId = (int) $this->context->getCart()->id;
        $db = null;

        try {
            if ($cartId > 0) {
                $db = \Db::getInstance();
                $db->execute('SELECT GET_LOCK("ps_checkout_cart_' . $cartId . '", 10)');

                $existingOrders = $this->orderRepository->getAllBy(['id_cart' => $cartId]);
                if (!empty($existingOrders)) {
                    foreach ($existingOrders as $order) {
                        $this->orderMatrixRepository->upsert((int) $order->id, $cartId);
                    }

                    return;
                }
            }

            $validateOrderData = $this->createValidateOrderDataAction->execute($payPalOrder);

            $this->validateOrderAction->execute($validateOrderData);

            $orders = $this->orderRepository->getAllBy(['id_cart' => $this->context->getCart()->id]);

            foreach ($orders as $order) {
                $this->orderMatrixRepository->upsert((int) $order->id, (int) $this->context->getCart()->id);
            }
        } catch (PsCheckoutException $exception) {
            throw $exception;
        } catch (\Exception $exception) {
            throw new PsCheckoutException($exception->getMessage(), PsCheckoutException::UNKNOWN);
        } finally {
            if ($db !== null) {
                $db->execute('SELECT RELEASE_LOCK("ps_checkout_cart_' . $cartId . '")');
            }
        }
    }
}
