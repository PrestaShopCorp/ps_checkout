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

    public $paypalOrder = null;
    public $psOrderId = null;

    public function __construct($paypalOrderId = null, $psOrderId = null)
    {
        if ($paypalOrderId === null || $psOrderId === null) {
            // TODO : Create paypal Order - Import class PaypalOrder.php
        }

        $this->paypalOrder = (new Maasland)->getOrderDetails($paypalOrderId);
        $this->psOrderId = $psOrderId;
    }

    /**
     * Process the validation for an order
     *
     * @param array $dataOrder array with all information required by PaymentModule->validateOrder()
     *
     * @return void
     */
    public function validateOrder($dataOrder)
    {
        $module = \Module::getInstanceByName('prestashoppayments');

        $module->validateOrder(
            $dataOrder['cartId'],
            $dataOrder['orderStateId'],
            $dataOrder['amount'],
            $dataOrder['paymentMethod'],
            $dataOrder['message'],
            $dataOrder['extraVars'],
            $dataOrder['currencyId'],
            false,
            $dataOrder['secureKey']
        );

        // TODO : patch the order in order to update the order id with the order id
        // of the prestashop order

        switch ($this->paypalOrder['intent']) {
            case INTENT_CAPTURE:
                $responseStatus = $this->captureOrder($this->paypalOrder['id']);
                break;
            case INTENT_AUTHORIZE:
                $responseStatus = $this->authorizeOrder($this->paypalOrder['id']);
                break;
        }

        $this->setOrderState($responseStatus);
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

        return $response['status'];
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

        return $response['status'];
    }

    /**
     * Set the status of the prestashop order if the payment has been
     * successfully captured or not
     *
     * @param string $status
     *
     * @return bool
     */
    public function setOrderState($status)
    {
        $order = new OrderHistory();
        $order->id_order = $this->psOrderId;

        if ($status === 'COMPLETED') {
            $order->changeIdOrderState(_PS_OS_PAYMENT_, $this->psOrderId);
        } else {
            $order->changeIdOrderState(_PS_OS_ERROR_, $this->psOrderId);
        }

        return $order->save();
    }
}
