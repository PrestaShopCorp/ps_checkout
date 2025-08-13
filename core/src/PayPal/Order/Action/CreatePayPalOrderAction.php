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
use PsCheckout\Core\Order\Builder\OrderPayloadBuilderInterface;
use PsCheckout\Core\PaymentToken\Action\DeletePaymentTokenActionInterface;
use PsCheckout\Core\PayPal\Customer\Repository\PayPalCustomerRepositoryInterface;
use PsCheckout\Core\PayPal\Order\Cache\PayPalOrderCacheInterface;
use PsCheckout\Core\PayPal\Order\Processor\CreatePayPalOrderProcessorInterface;
use PsCheckout\Core\PayPal\Order\Repository\PayPalOrderRepositoryInterface;
use PsCheckout\Core\PayPal\Order\Request\ValueObject\CreatePayPalOrderRequest;
use PsCheckout\Core\PayPal\Order\Response\ValueObject\CreatePayPalOrderResponse;
use PsCheckout\Infrastructure\Adapter\ContextInterface;
use PsCheckout\Presentation\Presenter\PresenterInterface;

class CreatePayPalOrderAction implements CreatePayPalOrderActionInterface
{
    /**
     * @var ContextInterface
     */
    private $context;

    /**
     * @var PayPalCustomerRepositoryInterface
     */
    private $payPalCustomerRepository;

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
     * @var PresenterInterface
     */
    private $cartPresenter;

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
        PayPalCustomerRepositoryInterface $payPalCustomerRepository,
        PayPalOrderRepositoryInterface $payPalOrderRepository,
        OrderHttpClientInterface $orderHttpClient,
        OrderPayloadBuilderInterface $orderPayloadBuilder,
        PresenterInterface $cartPresenter,
        CreatePayPalOrderProcessorInterface $createPayPalOrderProcessor,
        PayPalOrderCacheInterface $payPalOrderCache,
        DeletePaymentTokenActionInterface $deletePaymentTokenAction
    ) {
        $this->context = $context;
        $this->payPalCustomerRepository = $payPalCustomerRepository;
        $this->payPalOrderRepository = $payPalOrderRepository;
        $this->orderHttpClient = $orderHttpClient;
        $this->createPayPalOrderProcessor = $createPayPalOrderProcessor;
        $this->orderPayloadBuilder = $orderPayloadBuilder;
        $this->cartPresenter = $cartPresenter;
        $this->payPalOrderCache = $payPalOrderCache;
        $this->deletePaymentTokenAction = $deletePaymentTokenAction;
    }

    /**
     * {@inheritDoc}
     */
    public function execute(int $cartId, CreatePayPalOrderRequest $request): bool
    {
        $this->orderPayloadBuilder
            ->setCart($this->cartPresenter->present())
            ->setIsCard($this->isCardPayment($request))
            ->setIsExpressCheckout($request->isExpressCheckout())
            ->setFundingSource($request->getFundingSource())
            ->setSavePaymentMethod($request->isVault())
            ->setIsVault($request->getVaultId() || $request->isVault());

        if ($request->getVaultId()) {
            $this->orderPayloadBuilder->setPaypalVaultId($request->getVaultId());
        }

        $payPalCustomerId = null;

        if ($this->context->getCustomer()->id) {
            $payPalCustomerId = $this->payPalCustomerRepository->getPayPalCustomerIdByCustomerId($this->context->getCustomer()->id);
        }

        if ($payPalCustomerId) {
            $this->orderPayloadBuilder->setPaypalCustomerId($payPalCustomerId);
        }

        $payload = $this->orderPayloadBuilder->build();

        try {
            $orderResponse = $this->createPayPalOrder($payload);
        } catch (PayPalException $exception) {
            if ($request->getVaultId() && $exception->getCode() === PayPalException::CARD_CLOSED) {
                $this->deletePaymentTokenAction->execute(
                    $request->getVaultId(),
                    $this->context->getCustomer()->id
                );
            }

            throw $exception;
        }

        $this->deleteExistingPayPalOrder($cartId);

        $this->payPalOrderCache->updateOrderCache($orderResponse);

        $this->createPayPalOrderProcessor->createPayPalOrder($orderResponse, $request);

        return true;
    }

    /**
     * @param CreatePayPalOrderRequest $request
     *
     * @return bool
     */
    private function isCardPayment(CreatePayPalOrderRequest $request): bool
    {
        return $request->getFundingSource() === 'card' && ($request->isCardFields() || $request->getVaultId());
    }

    /**
     * @param array $payload
     *
     * @return CreatePayPalOrderResponse
     *
     * @throws PsCheckoutException
     */
    private function createPayPalOrder(array $payload): CreatePayPalOrderResponse
    {
        try {
            $response = $this->orderHttpClient->createOrder($payload);

            return CreatePayPalOrderResponse::createFromResponse(json_decode($response->getBody(), true));
        } catch (Exception $exception) {
            throw new PsCheckoutException('Failed to create order');
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
    private function deleteExistingPayPalOrder(int $cartId)
    {
        $existingOrder = $this->payPalOrderRepository->getOneByCartId($cartId);
        if ($existingOrder) {
            $this->payPalOrderRepository->deletePayPalOrder($existingOrder->getId());
        }
    }
}
