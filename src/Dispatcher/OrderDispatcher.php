<?php
/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License 3.0 (AFL-3.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 */

namespace PrestaShop\Module\PrestashopCheckout\Dispatcher;

use PrestaShop\Module\PrestashopCheckout\Exception\PsCheckoutException;
use PrestaShop\Module\PrestashopCheckout\Presenter\Date\DatePresenter;
use PrestaShop\Module\PrestashopCheckout\WebHookValidation;

class OrderDispatcher implements Dispatcher
{
    const PS_CHECKOUT_PAYMENT_REVERSED = 'PaymentCaptureReversed';
    const PS_CHECKOUT_PAYMENT_REFUNED = 'PaymentCaptureRefunded';
    const PS_CHECKOUT_PAYMENT_AUTH_VOIDED = 'PaymentAuthorizationVoided';
    const PS_CHECKOUT_PAYMENT_PENDING = 'PaymentCapturePending';
    const PS_CHECKOUT_PAYMENT_COMPLETED = 'PaymentCaptureCompleted';
    const PS_CHECKOUT_PAYMENT_DENIED = 'PaymentCaptureDenied';

    /**
     * @var \PsCheckoutCart
     */
    private $psCheckoutCart;

    /**
     * Dispatch the Event Type to manage the merchant status
     *
     * {@inheritdoc}
     *
     * @throws PsCheckoutException
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    public function dispatchEventType($payload)
    {
        if (empty($payload['orderId'])) {
            throw new PsCheckoutException('orderId must not be empty', PsCheckoutException::PSCHECKOUT_WEBHOOK_ORDER_ID_EMPTY);
        }

        $this->assignPsCheckoutCart($payload['orderId']);

        $psOrderId = \Order::getOrderByCartId((int) $this->psCheckoutCart->id_cart);

        if (false === $psOrderId) {
            throw new PsCheckoutException(sprintf('order #%s does not exist', $this->psCheckoutCart->paypal_order), PsCheckoutException::PRESTASHOP_ORDER_NOT_FOUND);
        }

        if ($payload['eventType'] === self::PS_CHECKOUT_PAYMENT_REFUNED
            || $payload['eventType'] === self::PS_CHECKOUT_PAYMENT_REVERSED) {
            return $this->dispatchPaymentAction($payload['eventType'], $payload['resource'], $psOrderId);
        }

        if ($payload['eventType'] === self::PS_CHECKOUT_PAYMENT_COMPLETED
            || $payload['eventType'] === self::PS_CHECKOUT_PAYMENT_DENIED
            || $payload['eventType'] === self::PS_CHECKOUT_PAYMENT_AUTH_VOIDED) {
            return $this->dispatchPaymentStatus($payload['eventType'], $payload['resource'], $psOrderId);
        }

        // For now, if pending, do not change anything
        if ($payload['eventType'] === self::PS_CHECKOUT_PAYMENT_PENDING) {
            return true;
        }

        return true;
    }

    /**
     * Dispatch the Event Type to the payments action Refunded or Revesed
     *
     * @param string $eventType
     * @param array $resource
     * @param int $orderId
     *
     * @return bool
     *
     * @throws PsCheckoutException
     */
    private function dispatchPaymentAction($eventType, $resource, $orderId)
    {
        (new WebHookValidation())->validateRefundResourceValues($resource);

        return true;

//        $initiateBy = 'Merchant';
//
//        if ($eventType === self::PS_CHECKOUT_PAYMENT_REVERSED) {
//            $initiateBy = 'Paypal';
//        }
//
//        return (new WebHookOrder($initiateBy, $resource, $orderId))->updateOrder();
    }

    /**
     * Dispatch the event Type the the payment status PENDING / COMPLETED / DENIED / AUTH_VOIDED
     *
     * @param string $eventType
     * @param array $resource
     * @param int $orderId
     *
     * @return bool
     *
     * @throws PsCheckoutException
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     * @throws \Exception
     */
    private function dispatchPaymentStatus($eventType, $resource, $orderId)
    {
        (new WebHookValidation())->validateRefundOrderIdValue($orderId);

        $order = new \Order($orderId);
        $currentOrderStateId = (int) $order->getCurrentState();
        $newOrderStateId = (int) $this->getNewState($eventType, $currentOrderStateId);

        // Prevent duplicate state entry
        if ($currentOrderStateId !== $newOrderStateId
            && false === (bool) $order->hasBeenPaid()
            && false === (bool) $order->hasBeenShipped()
            && false === (bool) $order->hasBeenDelivered()
            && false === (bool) $order->isInPreparation()
        ) {
            $orderHistory = new \OrderHistory();
            $orderHistory->id_order = $orderId;

            try {
                $orderHistory->changeIdOrderState(
                    $newOrderStateId,
                    $orderId
                );
                $orderHistory->addWithemail();
            } catch (\Exception $exception) {
                // If PrestaShop cannot send email or generate invoice do not break next operation
                // For example : https://github.com/PrestaShop/PrestaShop/issues/18837
            }
        }

        $orderPaymentCollection = $order->getOrderPaymentCollection();
        $orderPaymentCollection->where('amount', '=', $resource['amount']['value']);
        $shouldAddOrderPayment = true;

        /** @var \Ps_checkout $module */
        $module = \Module::getInstanceByName('ps_checkout');

        /** @var \PrestaShop\Module\PrestashopCheckout\FundingSource\FundingSourceTranslationProvider $fundingSourceTranslationProvider */
        $fundingSourceTranslationProvider = $module->getService('ps_checkout.funding_source.translation');

        /** @var \OrderPayment[] $orderPayments */
        $orderPayments = $orderPaymentCollection->getAll();
        foreach ($orderPayments as $orderPayment) {
            if (\Validate::isLoadedObject($orderPayment)) {
                if ($orderPayment->transaction_id !== $resource['id']) {
                    $orderPayment->transaction_id = $resource['id'];
                    $orderPayment->payment_method = $fundingSourceTranslationProvider->getPaymentMethodName($this->psCheckoutCart->paypal_funding);
                    try {
                        $orderPayment->save();
                    } catch (\Exception $exception) {
                        throw new PsCheckoutException('Cannot update OrderPayment', PsCheckoutException::PRESTASHOP_ORDER_PAYMENT, $exception);
                    }
                }
                $shouldAddOrderPayment = false;
            }
        }

        if (true === $shouldAddOrderPayment) {
            try {
                $order->addOrderPayment(
                    $resource['amount']['value'],
                    $fundingSourceTranslationProvider->getPaymentMethodName($this->psCheckoutCart->paypal_funding),
                    $resource['id'],
                    \Currency::getCurrencyInstance(\Currency::getIdByIsoCode($resource['amount']['currency_code'])),
                    (new DatePresenter($resource['create_time'], 'Y-m-d H:i:s'))->present()
                );
            } catch (\Exception $exception) {
                throw new PsCheckoutException('Cannot add OrderPayment', PsCheckoutException::PRESTASHOP_ORDER_PAYMENT, $exception);
            }
        }

        return true;
    }

    /**
     * @param string $eventType
     * @param int $currentOrderStateId
     *
     * @return int
     */
    private function getNewState($eventType, $currentOrderStateId)
    {
        if (static::PS_CHECKOUT_PAYMENT_AUTH_VOIDED === $eventType) {
            return (int) \Configuration::getGlobalValue('PS_OS_CANCELED');
        }

        if (static::PS_CHECKOUT_PAYMENT_COMPLETED === $eventType) {
            return $this->getPaidStatusId($currentOrderStateId);
        }

        if (static::PS_CHECKOUT_PAYMENT_DENIED === $eventType) {
            return (int) \Configuration::getGlobalValue('PS_OS_ERROR');
        }

        return $this->getPendingStatusId();
    }

    /**
     * @param int $currentOrderStateId Current OrderState identifier
     *
     * @return int OrderState paid identifier
     */
    private function getPaidStatusId($currentOrderStateId)
    {
        if ($currentOrderStateId === (int) \Configuration::getGlobalValue('PS_OS_OUTOFSTOCK_UNPAID')) {
            return (int) \Configuration::getGlobalValue('PS_OS_OUTOFSTOCK_PAID');
        }

        return (int) \Configuration::getGlobalValue('PS_OS_PAYMENT');
    }

    /**
     * @return int OrderState identifier
     */
    private function getPendingStatusId()
    {
        switch ($this->psCheckoutCart->paypal_funding) {
            case 'card':
                $orderStateId = (int) \Configuration::get('PS_CHECKOUT_STATE_WAITING_CREDIT_CARD_PAYMENT');
                break;
            case 'paypal':
                $orderStateId = (int) \Configuration::get('PS_CHECKOUT_STATE_WAITING_PAYPAL_PAYMENT');
                break;
            default:
                $orderStateId = (int) \Configuration::get('PS_CHECKOUT_STATE_WAITING_LOCAL_PAYMENT');
        }

        return $orderStateId;
    }

    /**
     * @param string $payPalOrderId PayPal Order Id
     *
     * @throws PsCheckoutException
     * @throws \PrestaShopException
     */
    private function assignPsCheckoutCart($payPalOrderId)
    {
        $psCheckoutCartCollection = new \PrestaShopCollection('PsCheckoutCart');
        $psCheckoutCartCollection->where('paypal_order', '=', $payPalOrderId);

        /* @var \PsCheckoutCart|false $psCheckoutCart */
        $this->psCheckoutCart = $psCheckoutCartCollection->getFirst();

        if (false === $this->psCheckoutCart) {
            throw new PsCheckoutException(sprintf('order #%s is not linked to a cart', $payPalOrderId), PsCheckoutException::PRESTASHOP_CART_NOT_FOUND);
        }
    }
}
