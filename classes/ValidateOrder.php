<?php
/**
* 2007-2019 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author    PrestaShop SA <contact@prestashop.com>
*  @copyright 2007-2019 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

namespace PrestaShop\Module\PrestashopCheckout;

use PrestaShop\Module\PrestashopCheckout\Api\Order;

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

    /**
     * @var string
     */
    private $paypalOrderId = null;

    /**
     * @var string
     */
    private $merchantId = null;

    public function __construct($paypalOrderId, $merchantId)
    {
        $this->setMerchantId($merchantId);
        $this->setPaypalOrderId($paypalOrderId);
    }

    /**
     * Process the validation for an order
     *
     * @param array $payload array with all information required by PaymentModule->validateOrder()
     */
    public function validateOrder($payload)
    {
        $module = \Module::getInstanceByName('ps_checkout');

        $module->validateOrder(
            $payload['cartId'],
            $this->getPendingStatusId($payload['paymentMethod']),
            $payload['amount'],
            $this->getPaymentMessageTranslation($payload['paymentMethod']),
            null,
            $payload['extraVars'],
            $payload['currencyId'],
            false,
            $payload['secureKey']
        );

        // TODO : patch the order in order to update the order id with the order id
        // of the prestashop order

        $paypalOrder = new PaypalOrder($this->paypalOrderId);
        $order = $paypalOrder->getOrder();
        $apiOrder = new Order(\Context::getContext()->link);

        switch ($paypalOrder->getOrderIntent()) {
            case self::INTENT_CAPTURE:
                $responseStatus = $apiOrder->capture($order['id'], $this->merchantId);
                break;
            case self::INTENT_AUTHORIZE:
                $responseStatus = $apiOrder->authorize($order['id'], $this->merchantId);
                break;
            default:
                throw new \Exception(sprintf('Unknown Intent type %s', $paypalOrder->getOrderIntent()));
        }

        $orderState = $this->setOrderState($module->currentOrder, $responseStatus, $payload['message']);

        if ($orderState === _PS_OS_PAYMENT_) {
            $this->setTransactionId($module->currentOrder, $payload['extraVars']['transaction_id']);
        }
    }

    /**
     * Get payment message
     *
     * @param string $paymentMethod can be 'paypal' or 'card'
     *
     * @return string translation
     */
    private function getPaymentMessageTranslation($paymentMethod)
    {
        $paymentMessage = 'Payment by card';

        if ($paymentMethod === 'paypal') {
            $paymentMessage = 'Payment by PayPal';
        }

        return $paymentMessage;
    }

    /**
     * Set the transactionId (paypal order id) to the payment associated to the order
     *
     * @param int $orderPrestashopId from prestashop
     * @param string $transactionId paypal order id
     *
     * @return bool
     */
    private function setTransactionId($orderPrestashopId, $orderPaypalId)
    {
        $orderMatrice = new OrderMatrice();
        $orderMatrice->id_order_prestashop = $orderPrestashopId;
        $orderMatrice->id_order_paypal = $orderPaypalId;

        return $orderMatrice->add();
    }

    /**
     * Set the status of the prestashop order if the payment has been
     * successfully captured or not
     *
     * @param int $orderId from prestashop
     * @param string $status
     * @param string $paymentMethod can be 'paypal' or 'card'
     *
     * @return int order state id to set to the order depending on the status return by paypal
     */
    private function setOrderState($orderId, $status, $paymentMethod)
    {
        $order = new \OrderHistory();
        $order->id_order = $orderId;

        switch ($status) {
            case self::CAPTURE_STATUS_COMPLETED:
                $orderState = _PS_OS_PAYMENT_;
                break;
            case self::CAPTURE_STATUS_DECLINED:
                $orderState = _PS_OS_ERROR_;
                break;
            case self::CAPTURE_STATUS_PENDING:
                $orderState = $this->getPendingStatusId($paymentMethod);
                break;
            default:
                $orderState = $this->getPendingStatusId($paymentMethod);
                break;
        }

        $order->changeIdOrderState($orderState, $orderId);
        $order->save();

        return $orderState;
    }

    /**
     * Set pending status depending on the method used to make the payment
     *
     * @param string $paymentMethod can be 'paypal' or 'card'
     *
     * @return int id state
     */
    private function getPendingStatusId($paymentMethod)
    {
        $stateId = \Configuration::get('PS_CHECKOUT_STATE_WAITING_CREDIT_CARD_PAYMENT');

        if ($paymentMethod === 'paypal') {
            $stateId = \Configuration::get('PS_CHECKOUT_STATE_WAITING_PAYPAL_PAYMENT');
        }

        return $stateId;
    }

    /**
     * setter for merchantId
     *
     * @param string $merchantId
     */
    public function setMerchantId($merchantId)
    {
        $this->merchantId = $merchantId;
    }

    /**
     * setter for orderId
     *
     * @param string $orderId
     */
    public function setPaypalOrderId($orderId)
    {
        $this->paypalOrderId = $orderId;
    }
}
