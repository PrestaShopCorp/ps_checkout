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

namespace PsCheckout\Core\PayPal\Order\Processor;

use Exception;
use PsCheckout\Api\Http\HttpClientInterface;
use PsCheckout\Api\Http\OrderHttpClientInterface;
use PsCheckout\Core\Order\Builder\OrderPayloadBuilderInterface;
use PsCheckout\Core\PayPal\Order\Action\UpdatePayPalOrderPurchaseUnitActionInterface;
use PsCheckout\Core\PayPal\Order\Cache\PayPalOrderCacheInterface;
use PsCheckout\Core\PayPal\Order\Exception\PayPalOrderException;
use PsCheckout\Core\PayPal\Order\Provider\PayPalOrderProviderInterface;
use PsCheckout\Core\PayPal\Order\Repository\PayPalOrderRepositoryInterface;
use PsCheckout\Core\PayPal\Order\Request\ValueObject\CheckPayPalOrderRequest;
use PsCheckout\Presentation\Presenter\PresenterInterface;
use PsCheckout\Utility\Common\ArrayUtility;

class UpdateExternalPayPalOrderProcessor implements UpdateExternalPayPalOrderProcessorInterface
{
    const FUNDING_SOURCE_CARD = 'card';

    /**
     * @var PayPalOrderProviderInterface
     */
    private $paypalOrderProvider;

    /**
     * @var PresenterInterface
     */
    private $cartPresenter;

    /**
     * @var OrderPayloadBuilderInterface
     */
    private $orderPayloadBuilder;

    /**
     * @var HttpClientInterface
     */
    private $httpClient;

    /**
     * @var PayPalOrderRepositoryInterface
     */
    private $paypalOrderRepository;

    /**
     * @var PayPalOrderCacheInterface
     */
    private $paypalOrderCache;

    /**
     * @var UpdatePayPalOrderPurchaseUnitActionInterface
     */
    private $updatePayPalOrderPurchaseUnit;

    public function __construct(
        PayPalOrderProviderInterface $paypalOrderProvider,
        PresenterInterface $cartPresenter,
        OrderPayloadBuilderInterface $orderPayloadBuilder,
        OrderHttpClientInterface $httpClient,
        PayPalOrderRepositoryInterface $paypalOrderRepository,
        PayPalOrderCacheInterface $paypalOrderCache,
        UpdatePayPalOrderPurchaseUnitActionInterface $updatePayPalOrderPurchaseUnit
    ) {
        $this->paypalOrderProvider = $paypalOrderProvider;
        $this->cartPresenter = $cartPresenter;
        $this->orderPayloadBuilder = $orderPayloadBuilder;
        $this->httpClient = $httpClient;
        $this->paypalOrderRepository = $paypalOrderRepository;
        $this->paypalOrderCache = $paypalOrderCache;
        $this->updatePayPalOrderPurchaseUnit = $updatePayPalOrderPurchaseUnit;
    }

    /**
     * {@inheritDoc}
     */
    public function execute(CheckPayPalOrderRequest $request)
    {
        $payPalOrder = $this->paypalOrderRepository->getOneBy(['id' => $request->getOrderId()]);

        if (!$payPalOrder) {
            return;
        }

        try {
            $paypalOrderResponse = $this->paypalOrderProvider->getById($request->getOrderId());
        } catch (Exception $exception) {
            return;
        }

        if (!$paypalOrderResponse->getPurchaseUnits()) {
            return;
        }

        $this->orderPayloadBuilder
            ->setCart($this->cartPresenter->present())
            ->setIsUpdate(true)
            ->setPaypalOrderId($request->getOrderId())
            ->setIsCard($request->getFundingSource() === self::FUNDING_SOURCE_CARD && $request->isHostedFields())
            ->setIsExpressCheckout($request->isExpressCheckout());

        $payload = $this->orderPayloadBuilder->build();

        $needToUpdate = false;

        if ($paypalOrderResponse->getOrderAmount() && isset($payload['amount'])) {
            $amountDiff = ArrayUtility::arrayRecursiveDiff($paypalOrderResponse->getOrderAmount(), $payload['amount']);
            if (!empty($amountDiff)) {
                $needToUpdate = true;
            }
        }

        if ($paypalOrderResponse->getItems() && isset($payload['items'])) {
            $itemsDiff = ArrayUtility::arrayRecursiveDiff($paypalOrderResponse->getItems(), $payload['items']);
            if (!empty($itemsDiff)) {
                $needToUpdate = true;
            }
        }

        if (isset($paypalOrderResponse->getPurchaseUnits()['shipping'], $payload['shipping'])) {
            $shippingDiff = ArrayUtility::arrayRecursiveDiff($paypalOrderResponse->getPurchaseUnits()['shipping'], $payload['shipping']);
            if (!empty($shippingDiff)) {
                $needToUpdate = true;
            }
        }

        if (!$needToUpdate) {
            return;
        }

        $response = $this->httpClient->updateOrder($payload);

        if ($response->getStatusCode() !== 204) {
            throw new PayPalOrderException('Failed to update PayPal Order', PayPalOrderException::PAYPAL_ORDER_UPDATE_FAILED);
        }

        $payPalOrder->setStatus($paypalOrderResponse->getStatus());
        $payPalOrder->setFundingSource($request->getFundingSource());
        $payPalOrder->setIsCardFields($request->isHostedFields());
        $payPalOrder->setIsExpressCheckout($request->isExpressCheckout());

        if ($paypalOrderResponse->getPaymentSource()) {
            $payPalOrder->setPaymentSource($paypalOrderResponse->getPaymentSource());
        }

        $this->paypalOrderRepository->save($payPalOrder);
        $this->updatePayPalOrderPurchaseUnit->execute($paypalOrderResponse);

        $this->paypalOrderCache->delete($request->getOrderId());
    }
}
