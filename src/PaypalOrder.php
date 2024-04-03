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

use Module;
use PrestaShop\Module\PrestashopCheckout\Exception\PayPalException;
use PrestaShop\Module\PrestashopCheckout\Handler\Response\ResponseApiHandler;
use PrestaShop\Module\PrestashopCheckout\Http\MaaslandHttpClient;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\Entity\PayPalOrder as PayPalOrderEntity;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\ValueObject\PayPalOrderId;
use PrestaShop\Module\PrestashopCheckout\PayPal\PayPalConfiguration;
use PrestaShop\Module\PrestashopCheckout\Repository\PayPalOrderRepository;
use Ps_checkout;

/**
 * Allow to instantiate a paypal order
 */
class PaypalOrder
{
    /**
     * @var array
     */
    private $order;

    /**
     * @param string $id
     */
    public function __construct($id)
    {
        $this->loadOrder($id);
    }

    /**
     * Load paypal order data
     *
     * @param string $id PayPal Order identifier
     */
    private function loadOrder($id)
    {
        /** @var Ps_checkout $module */
        $module = Module::getInstanceByName('ps_checkout');

        /** @var MaaslandHttpClient $maaslandHttpClient */
        $maaslandHttpClient = $module->getService(MaaslandHttpClient::class);

        /** @var PayPalOrderRepository $payPalOrderRepository */
        $payPalOrderRepository = $module->getService(PayPalOrderRepository::class);

        /** @var PayPalConfiguration $payPalConfiguration */
        $payPalConfiguration = $module->getService(PayPalConfiguration::class);

        $order = $payPalOrderRepository->getPayPalOrderById(new PayPalOrderId($id));

        try {
            $payload = [
                'orderId' => $id,
            ];

            if ($order->checkCustomerIntent(PayPalOrderEntity::CUSTOMER_INTENT_USES_VAULTING)) {
                $payload = array_merge($payload, [
                    'vault' => true,
                    'payee' => [
                        'merchant_id' => $payPalConfiguration->getMerchantId(),
                    ],
                ]);
            }
            $response = $maaslandHttpClient->fetchOrder($payload);
            $responseHandler = new ResponseApiHandler();
            $response = $responseHandler->handleResponse($response);

            if (true === $response['status'] && !empty($response['body'])) {
                $this->setOrder($response['body']);
            }
        } catch (PayPalException $exception) {
            if ($exception->getCode() === PayPalException::INVALID_RESOURCE_ID) {
                \Db::getInstance()->update(
                    \PsCheckoutCart::$definition['table'],
                    [
                        'paypal_status' => \PsCheckoutCart::STATUS_CANCELED,
                    ],
                    'paypal_order = "' . pSQL($id) . '"'
                );
                \Db::getInstance()->update(
                    \PrestaShop\Module\PrestashopCheckout\PayPal\Order\Entity\PayPalOrder::TABLE,
                    [
                        'status' => \PsCheckoutCart::STATUS_CANCELED,
                    ],
                    'id = "' . pSQL($id) . '"'
                );
            }
        }
    }

    /**
     * Getter the intent of an order (CAPTURE or AUTHORIZE)
     *
     * @return string intent of the order
     */
    public function getOrderIntent()
    {
        return $this->order['intent'];
    }

    /**
     * getter for the order
     *
     * @return array
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * setter for order
     *
     * @param array $order
     */
    public function setOrder($order)
    {
        $this->order = $order;
    }

    /**
     * @return bool
     */
    public function isLoaded()
    {
        return false === empty($this->order);
    }
}
