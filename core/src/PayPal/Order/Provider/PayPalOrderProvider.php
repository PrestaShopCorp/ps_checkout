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

namespace PsCheckout\Core\PayPal\Order\Provider;

use PsCheckout\Api\Http\Exception\PayPalException;
use PsCheckout\Api\Http\OrderHttpClientInterface;
use PsCheckout\Api\ValueObject\PayPalOrderResponse;
use PsCheckout\Core\Exception\PsCheckoutException;
use PsCheckout\Core\PayPal\Order\Cache\PayPalOrderCacheInterface;
use PsCheckout\Core\PayPal\Order\Entity\PayPalOrder;
use PsCheckout\Core\PayPal\Order\Exception\PayPalOrderException;
use PsCheckout\Core\PayPal\Order\Repository\PayPalOrderRepositoryInterface;
use PsCheckout\Core\PayPal\OrderStatus\Configuration\PayPalOrderStatusConfiguration;
use PsCheckout\Core\Settings\Configuration\PayPalConfiguration;
use PsCheckout\Infrastructure\Adapter\ConfigurationInterface;
use PsCheckout\Infrastructure\Repository\PayPalOrderRepository;

class PayPalOrderProvider implements PayPalOrderProviderInterface
{
    /**
     * @var ConfigurationInterface
     */
    private $configuration;

    /**
     * @var PayPalOrderCacheInterface
     */
    private $payPalOrderCache;

    /**
     * @var PayPalOrderRepositoryInterface
     */
    private $payPalOrderRepository;

    /**
     * @var OrderHttpClientInterface
     */
    private $orderHttpClient;

    /**
     * @param ConfigurationInterface $configuration
     * @param PayPalOrderCacheInterface $payPalOrderCache
     * @param PayPalOrderRepositoryInterface $payPalOrderRepository
     * @param OrderHttpClientInterface $orderHttpClient
     */
    public function __construct(
        ConfigurationInterface $configuration,
        PayPalOrderCacheInterface $payPalOrderCache,
        PayPalOrderRepositoryInterface $payPalOrderRepository,
        OrderHttpClientInterface $orderHttpClient
    ) {
        $this->configuration = $configuration;
        $this->payPalOrderCache = $payPalOrderCache;
        $this->payPalOrderRepository = $payPalOrderRepository;
        $this->orderHttpClient = $orderHttpClient;
    }

    /**
     * {@inheritDoc}
     */
    public function getById(string $id): PayPalOrderResponse
    {
        if (empty($id)) {
            throw new PayPalOrderException('Paypal order id is not provided', PayPalOrderException::INVALID_ID);
        }

        $data = [];

        if ($this->payPalOrderCache->has($id)) {
            $data = $this->payPalOrderCache->getValue($id);
        }

        if (!empty($data) && in_array($data['status'], ['COMPLETED', 'CANCELED'])) {
            return $this->buildPayPalOrderResponse($data);
        }

        $payPalOrderResponse = $this->fetchOrder($id);

        $orderToStoreInCache = !empty($data) ? array_replace_recursive($data, $payPalOrderResponse) : $payPalOrderResponse;

        if (!$orderToStoreInCache) {
            throw new PsCheckoutException('PayPal Order not found', PsCheckoutException::PAYPAL_ORDER_NOT_FOUND);
        }

        $this->payPalOrderCache->set($id, $orderToStoreInCache);

        return $this->buildPayPalOrderResponse($orderToStoreInCache);
    }

    /**
     * @param string $id
     *
     * @return array|null
     */
    private function fetchOrder(string $id)
    {
        $data = null;

        $payPalOrder = $this->payPalOrderRepository->getOneBy(['id' => $id]);

        $payload = [
            'orderId' => $id,
        ];

        if ($payPalOrder && $payPalOrder->checkCustomerIntent(PayPalOrder::CUSTOMER_INTENT_USES_VAULTING)) {
            $payload['vault'] = true;
            $payload['payee'] = [
                'merchant_id' => $this->configuration->get(PayPalConfiguration::PS_CHECKOUT_PAYPAL_ID_MERCHANT),
            ];
        }

        try {
            $response = $this->orderHttpClient->fetchOrder($payload);
            $responseData = json_decode($response->getBody(), true);

            if ($response->getStatusCode() >= 200 && $response->getStatusCode() < 300 && !empty($responseData)) {
                $data = $responseData;
            }
        } catch (PayPalException $exception) {
            if ($exception->getCode() === PayPalException::INVALID_RESOURCE_ID) {
                \Db::getInstance()->update(
                    PayPalOrderRepository::TABLE_NAME,
                    [
                        'status' => PayPalOrderStatusConfiguration::STATUS_CANCELED,
                    ],
                    'id = "' . pSQL($id) . '"'
                );
            }
        }

        return $data;
    }

    /**
     * @param array $data
     *
     * @return PayPalOrderResponse
     */
    private function buildPayPalOrderResponse(array $data): PayPalOrderResponse
    {
        return new PayPalOrderResponse(
            $data['id'],
            $data['status'],
            $data['intent'],
            $data['payer'] ?? null,
            $data['payment_source'] ?? null,
            $data['purchase_units'],
            $data['links'],
            $data['create_time']
        );
    }
}
