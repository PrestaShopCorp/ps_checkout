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

namespace PsCheckout\Core\PayPal\Order\Action;

use Exception;
use PrestaShopException;
use PsCheckout\Api\Http\Exception\PayPalException;
use PsCheckout\Api\Http\OrderHttpClientInterface;
use PsCheckout\Core\Exception\PsCheckoutException;
use PsCheckout\Core\Order\Builder\CheckoutContextBuilderInterface;
use PsCheckout\Core\Order\Builder\OrderPayloadBuilderInterface;
use PsCheckout\Core\PaymentToken\Action\DeletePaymentTokenActionInterface;
use PsCheckout\Core\PayPal\Order\Cache\PayPalOrderCacheInterface;
use PsCheckout\Core\PayPal\Order\Processor\CreatePayPalOrderProcessorInterface;
use PsCheckout\Core\PayPal\Order\Repository\PayPalOrderRepositoryInterface;
use PsCheckout\Core\PayPal\Order\Request\ValueObject\CreatePayPalOrderRequest;
use PsCheckout\Core\PayPal\Order\Response\ValueObject\CreatePayPalOrderResponse;
use PsCheckout\Infrastructure\Adapter\ContextInterface;

class CreatePayPalOrderAction implements CreatePayPalOrderActionInterface
{
    /**
     * @var ContextInterface
     */
    private $context;

    /**
     * @var PayPalOrderRepositoryInterface
     */
    private $payPalOrderRepository;

    /**
     * @var OrderHttpClientInterface
     */
    private $orderHttpClient;

    /**
     * @var CreatePayPalOrderProcessorInterface
     */
    private $createPayPalOrderProcessor;

    /**
     * @var OrderPayloadBuilderInterface
     */
    private $orderPayloadBuilder;

    /**
     * @var CheckoutContextBuilderInterface
     */
    private $checkoutContextBuilder;

    /**
     * @var PayPalOrderCacheInterface
     */
    private $payPalOrderCache;

    /**
     * @var DeletePaymentTokenActionInterface
     */
    private $deletePaymentTokenAction;

    public function __construct(
        ContextInterface $context,
        PayPalOrderRepositoryInterface $payPalOrderRepository,
        OrderHttpClientInterface $orderHttpClient,
        OrderPayloadBuilderInterface $orderPayloadBuilder,
        CheckoutContextBuilderInterface $checkoutContextBuilder,
        CreatePayPalOrderProcessorInterface $createPayPalOrderProcessor,
        PayPalOrderCacheInterface $payPalOrderCache,
        DeletePaymentTokenActionInterface $deletePaymentTokenAction
    ) {
        $this->context = $context;
        $this->payPalOrderRepository = $payPalOrderRepository;
        $this->orderHttpClient = $orderHttpClient;
        $this->createPayPalOrderProcessor = $createPayPalOrderProcessor;
        $this->orderPayloadBuilder = $orderPayloadBuilder;
        $this->checkoutContextBuilder = $checkoutContextBuilder;
        $this->payPalOrderCache = $payPalOrderCache;
        $this->deletePaymentTokenAction = $deletePaymentTokenAction;
    }

    /**
     * {@inheritDoc}
     */
    public function execute(int $cartId, CreatePayPalOrderRequest $request): bool
    {
        $this->checkoutContextBuilder
            ->setIsCard($this->isCardPayment($request))
            ->setIsExpressCheckout($request->isExpressCheckout())
            ->setFundingSource($request->getFundingSource())
            ->setSavePaymentMethod($request->isVault())
            ->setIsVault((bool) ($request->getVaultId() || $request->isVault()))
            ->setBirthDate($request->getBirthDate())
            ->setPhone($request->getPhone());

        if ($request->getVaultId()) {
            $this->checkoutContextBuilder->setPaypalVaultId($request->getVaultId());
        }

        $context = $this->checkoutContextBuilder->build();

        $payload = $this->orderPayloadBuilder->build($context);

        try {
            $clientMetadataId = $request->getFundingSource() === 'pay_upon_invoice' && $request->getMetaDataId()
                ? $request->getMetaDataId()
                : null;

            $orderResponse = $this->createPayPalOrder($payload, null, $clientMetadataId);
        } catch (PayPalException $exception) {
            if ($request->getVaultId() && $exception->getCode() === PayPalException::CARD_CLOSED) {
                $this->deletePaymentTokenAction->execute(
                    $request->getVaultId(),
                    $this->context->getCustomer()->id
                );
            }

            throw $exception;
        }

        $this->softDeleteExistingPayPalOrder($cartId);

        $this->payPalOrderCache->updateOrderCache($orderResponse);

        $this->createPayPalOrderProcessor->createPayPalOrder($orderResponse, $request);

        return true;
    }

    private function isCardPayment(CreatePayPalOrderRequest $request): bool
    {
        return $request->getFundingSource() === 'card' && ($request->isCardFields() || $request->getVaultId());
    }

    /**
     * @param array $payload
     * @param string|null $paypalRequestId
     * @param string|null $clientMetadataId
     *
     * @return CreatePayPalOrderResponse
     *
     * @throws PsCheckoutException
     */
    private function createPayPalOrder(array $payload, ?string $paypalRequestId = null, ?string $clientMetadataId = null): CreatePayPalOrderResponse
    {
        try {
            $response = $this->orderHttpClient->createOrder($payload, $paypalRequestId, $clientMetadataId);

            return CreatePayPalOrderResponse::createFromResponse(json_decode($response->getBody(), true));
        } catch (Exception $exception) {
            throw new PsCheckoutException('Failed to create order', PsCheckoutException::UNKNOWN, $exception);
        }
    }

    /**
     * @param int $cartId
     *
     * @return void
     *
     * @throws PrestaShopException
     * @throws PsCheckoutException
     */
    private function softDeleteExistingPayPalOrder(int $cartId)
    {
        $existingOrder = $this->payPalOrderRepository->getOneByCartId($cartId);
        if ($existingOrder) {
            $this->payPalOrderRepository->softDelete($existingOrder);
        }
    }
}
