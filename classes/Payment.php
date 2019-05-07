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

namespace PrestaShop\Module\PrestashopPayments;

use PrestaShop\Module\PrestashopPayments\Api\Maasland;

class Payment
{
    const INTENT_CAPTURE = 'CAPTURE';
    const INTENT_AUTHORIZE = 'AUTHORIZE';

    public $paypalOrderId = null;
    public $paypalOrderDetail = null;

    public function __construct($paypalOrderId = null)
    {
        if ($paypalOrderId === null) {
            throw new \PrestaShopException('Paypal order id is required');
        }

        $this->paypalOrderId = $paypalOrderId;
    }

    /**
     * Load paypal order detail
     *
     * @param string $paypalOrderId
     *
     * @return void
     */
    public function loadPaypalOrderDetail()
    {
        if (null !== $this->paypalOrderDetail) {
            return false;
        }

        $paypalOrder = (new Maasland)->fetchOrder($this->paypalOrderId);

        if (false === $paypalOrder) {
            return false;
        }

        $this->paypalOrderDetail = $paypalOrder;
    }

    /**
     * Process the validation for an order
     *
     * @param array $payload array with all information required by PaymentModule->validateOrder()
     *
     * @return void
     */
    public function validateOrder($payload)
    {
        $module = \Module::getInstanceByName('prestashoppayments');

        $module->validateOrder(
            $payload['cartId'],
            $payload['orderStateId'],
            $payload['amount'],
            $payload['paymentMethod'],
            $payload['message'],
            $payload['extraVars'],
            $payload['currencyId'],
            false,
            $payload['secureKey']
        );

        // TODO : patch the order in order to update the order id with the order id
        // of the prestashop order

        $this->loadPaypalOrderDetail();

        switch ($this->paypalOrderDetail['intent']) {
            case self::INTENT_CAPTURE:
                $responseStatus = $this->captureOrder($this->paypalOrderDetail['id']);
                break;
            case self::INTENT_AUTHORIZE:
                $responseStatus = $this->authorizeOrder($this->paypalOrderDetail['id']);
                break;
            default:
                throw new \Exception(sprintf('Unknown Intent type %s', $this->paypalOrderDetail['intent']));
        }

        $orderState = $this->setOrderState($module->currentOrder, $responseStatus);

        if ($orderState === _PS_OS_PAYMENT_) {
            $this->setTransactionId($module->currentOrder, $payload['extraVars']['transaction_id']);
        }
    }

    /**
     * Refund order
     *
     * @param float $amount value to refund
     * @param string $currenctCode
     *
     * @return bool
     */
    public function refundOrder($amount, $currencyCode)
    {
        $this->loadPaypalOrderDetail();

        $purchaseUnits = current($this->paypalOrderDetail['purchase_units']);
        $capture = current($purchaseUnits['payments']['captures']);
        $captureId = $capture['id'];

        $payload = [
            'orderId' => $this->paypalOrderId,
            'captureId' => $captureId,
            'amount' =>
            [
                'currency_code' => $currencyCode,
                'value' => $amount
            ],
            'note_to_payer' => 'Refund by '.\Configuration::get('PS_SHOP_NAME')
        ];

        return (new Maasland)->refundOrder($payload);
    }

    /**
     * Return the status of the authorise
     *
     * @param string $orderId paypal order id
     *
     * @return string state of the order
     */
    public function authorizeOrder($orderId)
    {
        $maasland = new Maasland();
        $response = $maasland->authorizeOrder($orderId);

        return isset($response['status']) ? $response['status'] : false;
    }

    /**
     * Return the status of the capture
     *
     * @param string $orderId paypal order id
     *
     * @return string state of the order
     */
    public function captureOrder($orderId)
    {
        $maasland = new Maasland();
        $response = $maasland->captureOrder($orderId);

        return isset($response['status']) ? $response['status'] : false;
    }

    /**
     * Set the transactionId (paypal order id) to the payment associated to the order
     *
     * @param int $psOrderId from prestashop
     * @param string $transactionId paypal order id
     *
     * @return bool
     */
    public function setTransactionId($psOrderId, $transactionId)
    {
        $paymentOrder = \OrderPayment::getByOrderId($psOrderId);

        if (false === is_array($paymentOrder)) {
            return false;
        }

        $paymentOrder = current($paymentOrder);
        $paymentOrder = new \OrderPayment($paymentOrder->id);
        $paymentOrder->transaction_id = $transactionId;

        return $paymentOrder->save();
    }

    /**
     * Set the status of the prestashop order if the payment has been
     * successfully captured or not
     *
     * @param int $orderId from prestashop
     * @param string $status
     *
     * @return int order state id to set to the order depending on the status return by paypal
     */
    public function setOrderState($orderId, $status)
    {
        $order = new \OrderHistory();
        $order->id_order = $orderId;

        if ($status === 'COMPLETED') {
            $orderState = _PS_OS_PAYMENT_;
        } else {
            $orderState = _PS_OS_ERROR_;
        }

        $order->changeIdOrderState(_PS_OS_PAYMENT_, $orderId);
        $order->save();

        return $orderState;
    }
}
