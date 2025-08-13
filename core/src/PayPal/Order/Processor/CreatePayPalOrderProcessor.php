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

use PrestaShopException;
use PsCheckout\Core\Exception\PsCheckoutException;
use PsCheckout\Core\PaymentToken\Repository\PaymentTokenRepositoryInterface;
use PsCheckout\Core\PayPal\Customer\Repository\PayPalCustomerRepositoryInterface;
use PsCheckout\Core\PayPal\Order\Entity\PayPalOrder;
use PsCheckout\Core\PayPal\Order\Entity\PayPalOrderAuthorization;
use PsCheckout\Core\PayPal\Order\Entity\PayPalOrderCapture;
use PsCheckout\Core\PayPal\Order\Entity\PayPalOrderPurchaseUnit;
use PsCheckout\Core\PayPal\Order\Entity\PayPalOrderRefund;
use PsCheckout\Core\PayPal\Order\Repository\PayPalOrderAuthorizationRepositoryInterface;
use PsCheckout\Core\PayPal\Order\Repository\PayPalOrderCaptureRepositoryInterface;
use PsCheckout\Core\PayPal\Order\Repository\PayPalOrderPurchaseUnitRepositoryInterface;
use PsCheckout\Core\PayPal\Order\Repository\PayPalOrderRefundRepositoryInterface;
use PsCheckout\Core\PayPal\Order\Repository\PayPalOrderRepositoryInterface;
use PsCheckout\Core\PayPal\Order\Request\ValueObject\CreatePayPalOrderRequest;
use PsCheckout\Core\PayPal\Order\Response\ValueObject\CreatePayPalOrderResponse;
use PsCheckout\Core\Settings\Configuration\PayPalConfiguration;
use PsCheckout\Infrastructure\Adapter\ConfigurationInterface;
use PsCheckout\Infrastructure\Adapter\ContextInterface;

class CreatePayPalOrderProcessor implements CreatePayPalOrderProcessorInterface
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
     * @var PaymentTokenRepositoryInterface
     */
    private $paymentTokenRepository;

    /**
     * @var PayPalOrderRepositoryInterface
     */
    private $payPalOrderRepository;

    /**
     * @var ConfigurationInterface
     */
    private $configuration;

    /**
     * @var PayPalOrderPurchaseUnitRepositoryInterface
     */
    private $payPalOrderPurchaseUnitRepository;

    /**
     * @var PayPalOrderCaptureRepositoryInterface
     */
    private $payPalOrderCaptureRepository;

    /**
     * @var PayPalOrderAuthorizationRepositoryInterface
     */
    private $payPalOrderAuthorizationRepository;

    /**
     * @var PayPalOrderRefundRepositoryInterface
     */
    private $payPalOrderRefundRepository;

    public function __construct(
        ContextInterface $context,
        PayPalCustomerRepositoryInterface $payPalCustomerRepository,
        PaymentTokenRepositoryInterface $paymentTokenRepository,
        PayPalOrderRepositoryInterface $payPalOrderRepository,
        ConfigurationInterface $configuration,
        PayPalOrderPurchaseUnitRepositoryInterface $payPalOrderPurchaseUnitRepository,
        PayPalOrderCaptureRepositoryInterface $payPalOrderCaptureRepository,
        PayPalOrderAuthorizationRepositoryInterface $payPalOrderAuthorizationRepository,
        PayPalOrderRefundRepositoryInterface $payPalOrderRefundRepository
    ) {
        $this->context = $context;
        $this->payPalCustomerRepository = $payPalCustomerRepository;
        $this->paymentTokenRepository = $paymentTokenRepository;
        $this->payPalOrderRepository = $payPalOrderRepository;
        $this->configuration = $configuration;
        $this->payPalOrderPurchaseUnitRepository = $payPalOrderPurchaseUnitRepository;
        $this->payPalOrderCaptureRepository = $payPalOrderCaptureRepository;
        $this->payPalOrderAuthorizationRepository = $payPalOrderAuthorizationRepository;
        $this->payPalOrderRefundRepository = $payPalOrderRefundRepository;
    }

    /**
     * {@inheritDoc}
     */
    public function createPayPalOrder(CreatePayPalOrderResponse $orderResponse, CreatePayPalOrderRequest $orderRequest)
    {
        $this->savePayPalOrder($orderResponse, $orderRequest);
        $this->savePurchaseUnits($orderResponse);
    }

    /**
     * @param CreatePayPalOrderResponse $orderResponse
     * @param CreatePayPalOrderRequest $request
     *
     * @return void
     *
     * @throws PrestaShopException
     */
    private function savePayPalOrder(
        CreatePayPalOrderResponse $orderResponse,
        CreatePayPalOrderRequest $request
    ) {
        $paypalOrder = new PayPalOrder(
            $orderResponse->getId(),
            $this->context->getCart()->id,
            $orderResponse->getIntent(),
            $request->getFundingSource(),
            $orderResponse->getStatus(),
            $orderResponse->getPaymentSource(),
            $this->configuration->get(PayPalConfiguration::PS_CHECKOUT_PAYMENT_MODE),
            $request->isCardFields(),
            $request->isExpressCheckout(),
            $this->getCustomerIntent($request, $this->getPayPalCustomerId()),
            $request->getVaultId()
        );

        $this->payPalOrderRepository->savePayPalOrder($paypalOrder);
    }

    /**
     * @param CreatePayPalOrderRequest $request
     * @param string|null $payPalCustomerId
     *
     * @return array
     *
     * @throws PsCheckoutException
     */
    private function getCustomerIntent(CreatePayPalOrderRequest $request, $payPalCustomerId): array
    {
        $customerIntent = [];

        if ($request->getVaultId()) {
            $customerIntent[] = PayPalConfiguration::PS_CHECKOUT_CUSTOMER_INTENT_USES_VAULTING;
            $this->validatePaymentToken($request->getVaultId(), $payPalCustomerId);
        }

        if ($request->isVault()) {
            $customerIntent[] = PayPalConfiguration::PS_CHECKOUT_CUSTOMER_INTENT_VAULT;
            $customerIntent[] = PayPalConfiguration::PS_CHECKOUT_CUSTOMER_INTENT_USES_VAULTING;
        }

        if ($request->isFavorite()) {
            $customerIntent[] = PayPalConfiguration::PS_CHECKOUT_CUSTOMER_INTENT_FAVORITE;
        }

        return $customerIntent;
    }

    /**
     * @return string|null
     */
    private function getPayPalCustomerId()
    {
        if (!$this->context->getCustomer()->id) {
            return null;
        }

        $paypalCustomerId = $this->payPalCustomerRepository
            ->getPayPalCustomerIdByCustomerId($this->context->getCustomer()->id);

        return $paypalCustomerId ?? null;
    }

    /**
     * @param string $vaultId
     * @param string|null $payPalCustomerId
     *
     * @return void
     *
     * @throws PsCheckoutException
     */
    private function validatePaymentToken(string $vaultId, $payPalCustomerId)
    {
        $paymentToken = $this->paymentTokenRepository->getOneById($vaultId);

        if (!$paymentToken || !$payPalCustomerId || $paymentToken->getPayPalCustomerId() !== $payPalCustomerId) {
            throw new PsCheckoutException('Payment token does not belong to the customer');
        }
    }

    /**
     * @param CreatePayPalOrderResponse $orderResponse
     *
     * @return void
     */
    private function savePurchaseUnits(CreatePayPalOrderResponse $orderResponse)
    {
        foreach ($orderResponse->getPurchaseUnits() as $purchaseUnit) {
            $payPalPurchaseUnit = new PayPalOrderPurchaseUnit(
                $orderResponse->getId(),
                crc32(json_encode($purchaseUnit)),
                $purchaseUnit['reference_id'],
                $purchaseUnit['items']
            );

            $this->payPalOrderPurchaseUnitRepository->save($payPalPurchaseUnit);

            $this->savePayments($purchaseUnit, $orderResponse->getId());
        }
    }

    /**
     * @param array $purchaseUnit
     * @param string $orderId
     *
     * @return void
     */
    private function savePayments(array $purchaseUnit, string $orderId)
    {
        if (!empty($purchaseUnit['payments']['captures'])) {
            foreach ($purchaseUnit['payments']['captures'] as $capture) {
                $payPalCapture = new PayPalOrderCapture(
                    $capture['id'],
                    $orderId,
                    $capture['status'],
                    $capture['create_time'],
                    $capture['update_time'],
                    $capture['seller_protection'],
                    $capture['seller_receivable_breakdown'],
                    (bool) $capture['final_capture']
                );
                $this->payPalOrderCaptureRepository->save($payPalCapture);
            }
        }

        if (!empty($purchaseUnit['payments']['authorizations'])) {
            foreach ($purchaseUnit['payments']['authorizations'] as $authorization) {
                $payPalAuthorization = new PayPalOrderAuthorization(
                    $authorization['id'],
                    $orderId,
                    $authorization['status'],
                    $authorization['expiration_time'],
                    $authorization['seller_protection']
                );
                $this->payPalOrderAuthorizationRepository->save($payPalAuthorization);
            }
        }

        if (!empty($purchaseUnit['payments']['refunds'])) {
            foreach ($purchaseUnit['payments']['refunds'] as $refund) {
                $payPalRefund = new PayPalOrderRefund(
                    $refund['id'],
                    $orderId,
                    $refund['status'],
                    $refund['invoice_id'],
                    $refund['custom_id'],
                    $refund['acquirer_reference_number'],
                    $refund['seller_payable_breakdown']
                );
                $this->payPalOrderRefundRepository->save($payPalRefund);
            }
        }
    }
}
