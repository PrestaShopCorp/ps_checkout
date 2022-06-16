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

namespace PrestaShop\Module\PrestashopCheckout;

use PrestaShop\Module\PrestashopCheckout\Api\Payment\Order;
use PrestaShop\Module\PrestashopCheckout\Exception\PsCheckoutException;
use PrestaShop\Module\PrestashopCheckout\Repository\PaypalAccountRepository;
use PrestaShop\Module\PrestashopCheckout\Updater\PaypalAccountUpdater;
use Psr\SimpleCache\CacheInterface;

/**
 * Class that allow to validate an order
 */
class ValidateOrder
{
    const INTENT_CAPTURE = 'CAPTURE';
    const INTENT_AUTHORIZE = 'AUTHORIZE';

    const CAPTURE_STATUS_PENDING = 'PENDING';
    const CAPTURE_STATUS_DENIED = 'DENIED';
    const CAPTURE_STATUS_VOIDED = 'VOIDED';
    const CAPTURE_STATUS_COMPLETED = 'COMPLETED';
    const CAPTURE_STATUS_DECLINED = 'DECLINED';

    const PAYMENT_METHOD_PAYPAL = 'paypal';
    const PAYMENT_METHOD_CARD = 'card';

    /**
     * @var string
     */
    private $paypalOrderId;

    /**
     * @var string
     */
    private $merchantId;

    /**
     * @var \Context
     */
    private $context;

    /**
     * @param string $paypalOrderId
     * @param string $merchantId
     */
    public function __construct($paypalOrderId, $merchantId)
    {
        $this->merchantId = $merchantId;
        $this->paypalOrderId = $paypalOrderId;
        $this->context = \Context::getContext();
    }

    /**
     * Process the validation for an order
     *
     * @param array $payload array with all information required by PaymentModule->validateOrder()
     *
     * @throws PsCheckoutException
     * @throws \PrestaShopException
     */
    public function validateOrder($payload)
    {
        /** @var \Ps_checkout $module */
        $module = \Module::getInstanceByName('ps_checkout');

        /** @var \PrestaShop\Module\PrestashopCheckout\Handler\ExceptionHandler $exceptionHandler */
        $exceptionHandler = $module->getService('ps_checkout.handler.exception');

        // API call here
        $paypalOrder = new PaypalOrder($this->paypalOrderId);
        $order = $paypalOrder->getOrder();

        if (empty($order)) {
            throw new PsCheckoutException(sprintf('Unable to retrieve Paypal Order for %s', $this->paypalOrderId), PsCheckoutException::PRESTASHOP_ORDER_NOT_FOUND);
        }

        $transactionIdentifier = false === empty($order['purchase_units'][0]['payments']['captures'][0]['id']) ? $order['purchase_units'][0]['payments']['captures'][0]['id'] : '';
        $transactionStatus = false === empty($order['purchase_units'][0]['payments']['captures'][0]['status']) ? $order['purchase_units'][0]['payments']['captures'][0]['status'] : '';

        // @todo To be refactored in v2.0.0 with Service Container
        if (true === empty($order['purchase_units'][0]['payments']['captures'])) {
            /** @var \Ps_checkout $module */
            $module = \Module::getInstanceByName('ps_checkout');

            /** @var \PrestaShop\Module\PrestashopCheckout\FundingSource\FundingSourceTranslationProvider $fundingSourceTranslationProvider */
            $fundingSourceTranslationProvider = $module->getService('ps_checkout.funding_source.translation');

            /** @var \PrestaShop\Module\PrestashopCheckout\Repository\PsCheckoutCartRepository $psCheckoutCartRepository */
            $psCheckoutCartRepository = $module->getService('ps_checkout.repository.pscheckoutcart');

            /** @var \PsCheckoutCart|false $psCheckoutCart */
            $psCheckoutCart = $psCheckoutCartRepository->findOneByCartId((int) $payload['cartId']);

            // Check if the PayPal order amount is the same than the cart amount
            // We tolerate a difference of more or less 0.05
            $paypalOrderAmount = sprintf('%01.2f', $order['purchase_units'][0]['amount']['value']);
            $cartAmount = sprintf('%01.2f', $this->context->cart->getOrderTotal(true, \Cart::BOTH));

            if ($paypalOrderAmount + 0.05 < $cartAmount || $paypalOrderAmount - 0.05 > $cartAmount) {
                throw new PsCheckoutException('The transaction amount doesn\'t match with the cart amount.', PsCheckoutException::DIFFERENCE_BETWEEN_TRANSACTION_AND_CART);
            }

            $apiOrder = new Order($this->context->link);

            $fundingSource = false === $psCheckoutCart ? 'paypal' : $psCheckoutCart->paypal_funding;

            if ($fundingSource === 'card') {
                $fundingSource .= $psCheckoutCart->isHostedFields ? '_hosted' : '_inline';
            }

            $response = $apiOrder->capture(
                $order['id'],
                $this->merchantId,
                $fundingSource
            ); // API call here

            if (false === $response['status']) {
                if (false === empty($response['body']['message'])) {
                    if ($response['body']['message'] === 'PAYEE_ACCOUNT_RESTRICTED') {
                        /** @var PaypalAccountRepository $payPalAccountRepository */
                        $payPalAccountRepository = $module->getService('ps_checkout.repository.paypal.account');
                        /** @var PaypalAccountUpdater $payPalAccountUpdater */
                        $payPalAccountUpdater = $module->getService('ps_checkout.updater.paypal.account');
                        $payPalAccount = $payPalAccountRepository->getOnboardedAccount();
                        $payPalAccount->setPaypalPaymentStatus(0);
                        $payPalAccountUpdater->update($payPalAccount);
                    }
                    (new PayPalError($response['body']['message']))->throwException();
                }

                if (false === empty($response['exceptionMessage']) && false === empty($response['exceptionCode'])) {
                    throw new PsCheckoutException($response['exceptionMessage'], (int) $response['exceptionCode']);
                }

                throw new PsCheckoutException(isset($response['body']['error']) ? $response['body']['error'] : 'Unknown error', PsCheckoutException::UNKNOWN);
            }

            if (false === empty($response['body']['purchase_units'][0]['payments']['captures'])) {
                $transactionIdentifier = $response['body']['purchase_units'][0]['payments']['captures'][0]['id'];
                $transactionStatus = $response['body']['purchase_units'][0]['payments']['captures'][0]['status'];

                if (self::CAPTURE_STATUS_DECLINED === $transactionStatus
                    && false === empty($response['body']['payment_source'])
                    && false === empty($response['body']['payment_source'][0]['card'])
                    && false === empty($response['body']['purchase_units'][0]['payments']['captures'][0]['processor_response'])
                ) {
                    $payPalProcessorResponse = new PayPalProcessorResponse(
                        isset($response['body']['payment_source'][0]['card']['brand']) ? $response['body']['payment_source'][0]['card']['brand'] : null,
                        isset($response['body']['payment_source'][0]['card']['type']) ? $response['body']['payment_source'][0]['card']['type'] : null,
                        isset($response['body']['purchase_units'][0]['payments']['captures'][0]['processor_response']['avs_code']) ? $response['body']['purchase_units'][0]['payments']['captures'][0]['processor_response']['avs_code'] : null,
                        isset($response['body']['purchase_units'][0]['payments']['captures'][0]['processor_response']['cvv_code']) ? $response['body']['purchase_units'][0]['payments']['captures'][0]['processor_response']['cvv_code'] : null,
                        isset($response['body']['purchase_units'][0]['payments']['captures'][0]['processor_response']['response_code']) ? $response['body']['purchase_units'][0]['payments']['captures'][0]['processor_response']['response_code'] : null
                    );
                    $payPalProcessorResponse->throwException();
                }
            }

            /** @var CacheInterface $paypalOrderCache */
            $paypalOrderCache = $module->getService('ps_checkout.cache.paypal.order');
            $paypalOrderCache->set($response['body']['id'], $response['body']);

            if (false === $psCheckoutCart) {
                $psCheckoutCart = new \PsCheckoutCart();
                $psCheckoutCart->id_cart = (int) $payload['cartId'];
                $psCheckoutCart->paypal_intent = $paypalOrder->getOrderIntent();
                $psCheckoutCart->paypal_order = $response['body']['id'];
                $psCheckoutCart->paypal_status = $response['body']['status'];
                $psCheckoutCartRepository->save($psCheckoutCart);
            } else {
                $psCheckoutCart->paypal_order = $response['body']['id'];
                $psCheckoutCart->paypal_status = $response['body']['status'];
                $psCheckoutCartRepository->save($psCheckoutCart);
            }

            if (self::CAPTURE_STATUS_DECLINED === $transactionStatus) {
                throw new PsCheckoutException(sprintf('Transaction declined by PayPal : %s', false === empty($response['body']['details']['description']) ? $response['body']['details']['description'] : 'No detail'), PsCheckoutException::PAYPAL_PAYMENT_CAPTURE_DECLINED);
            }

            try {
                $module->validateOrder(
                    $payload['cartId'],
                    (int) $this->getOrderState($psCheckoutCart->paypal_funding),
                    $payload['amount'],
                    $fundingSourceTranslationProvider->getPaymentMethodName($psCheckoutCart->paypal_funding),
                    null,
                    [
                        'transaction_id' => $transactionIdentifier,
                    ],
                    $payload['currencyId'],
                    false,
                    $payload['secureKey']
                );
            } catch (\ErrorException $exception) {
                // Notice or warning from PHP
                $exceptionHandler->handle($exception, false);
            } catch (\Exception $exception) {
                $exceptionHandler->handle(new PsCheckoutException('PrestaShop cannot validate order', PsCheckoutException::PRESTASHOP_VALIDATE_ORDER, $exception));
            }

            if (empty($module->currentOrder)) {
                throw new PsCheckoutException(sprintf('PrestaShop was unable to returns Prestashop Order ID for Prestashop Cart ID : %s  - Paypal Order ID : %s. This happens when PrestaShop take too long time to create an Order due to heavy processes in hooks actionValidateOrder and/or actionOrderStatusUpdate and/or actionOrderStatusPostUpdate', $payload['cartId'], $this->paypalOrderId), PsCheckoutException::PRESTASHOP_ORDER_ID_MISSING);
            }

            if (false === $this->setOrdersMatrice($module->currentOrder, $this->paypalOrderId)) {
                throw new PsCheckoutException(sprintf('Set Order Matrice error for Prestashop Order ID : %s and Paypal Order ID : %s', $module->currentOrder, $this->paypalOrderId), PsCheckoutException::PSCHECKOUT_ORDER_MATRICE_ERROR);
            }

            if (in_array($transactionStatus, [static::CAPTURE_STATUS_COMPLETED, static::CAPTURE_STATUS_DECLINED])) {
                $newOrderState = static::CAPTURE_STATUS_COMPLETED === $transactionStatus ? $this->getPaidStatusId($module->currentOrder) : (int) \Configuration::getGlobalValue('PS_OS_ERROR');

                $orderPS = new \Order($module->currentOrder);
                $currentOrderStateId = (int) $orderPS->getCurrentState();

                // If have to change current OrderState from Waiting to Paid or Canceled
                if ($currentOrderStateId !== $newOrderState) {
                    $orderHistory = new \OrderHistory();
                    $orderHistory->id_order = $module->currentOrder;
                    try {
                        $orderHistory->changeIdOrderState($newOrderState, $module->currentOrder);
                        $orderHistory->addWithemail();
                    } catch (\ErrorException $exception) {
                        // Notice or warning from PHP
                        // For example : https://github.com/PrestaShop/PrestaShop/issues/18837
                        $exceptionHandler->handle($exception, false);
                    } catch (\Exception $exception) {
                        $exceptionHandler->handle(new PsCheckoutException('Unable to change PrestaShop OrderState', PsCheckoutException::PRESTASHOP_ORDER_STATE_ERROR, $exception));
                    }
                }
            }
        }

        return [
            'status' => false === empty($response) ? $response['body']['status'] : $order['status'],
            'paypalOrderId' => false === empty($response) ? $response['body']['id'] : $this->paypalOrderId,
            'transactionIdentifier' => $transactionIdentifier,
        ];
    }

    /**
     * @todo To remove when need of fallback on previous version is gone
     *
     * Set the matrice order values
     *
     * @param int $orderPrestashopId from prestashop
     * @param string $orderPaypalId paypal order id
     *
     * @return bool
     *
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    private function setOrdersMatrice($orderPrestashopId, $orderPaypalId)
    {
        $orderMatrice = new \OrderMatrice();
        $orderMatrice->id_order_prestashop = $orderPrestashopId;
        $orderMatrice->id_order_paypal = $orderPaypalId;

        return $orderMatrice->add();
    }

    /**
     * @param int $orderId Order identifier
     *
     * @return int OrderState identifier
     *
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    private function getPaidStatusId($orderId)
    {
        $order = new \Order($orderId);

        if (\Validate::isLoadedObject($order) && $order->getCurrentState() == \Configuration::getGlobalValue('PS_OS_OUTOFSTOCK_UNPAID')) {
            return (int) \Configuration::getGlobalValue('PS_OS_OUTOFSTOCK_PAID');
        }

        return (int) \Configuration::getGlobalValue('PS_OS_PAYMENT');
    }

    /**
     * Get OrderState identifier
     *
     * @todo Move to a dedicated Service
     *
     * @param string $fundingSource
     *
     * @return int
     */
    private function getOrderState($fundingSource)
    {
        switch ($fundingSource) {
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
}
