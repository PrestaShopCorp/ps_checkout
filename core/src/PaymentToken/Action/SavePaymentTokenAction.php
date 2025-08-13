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

namespace PsCheckout\Core\PaymentToken\Action;

use PsCheckout\Api\ValueObject\PayPalOrderResponse;
use PsCheckout\Core\PaymentToken\Repository\PaymentTokenRepositoryInterface;
use PsCheckout\Core\PaymentToken\ValueObject\PaymentToken;
use PsCheckout\Core\PayPal\Customer\Repository\PayPalCustomerRepositoryInterface;
use PsCheckout\Core\PayPal\Order\Repository\PayPalOrderRepositoryInterface;
use PsCheckout\Core\Settings\Configuration\PayPalConfiguration;
use PsCheckout\Infrastructure\Adapter\ConfigurationInterface;
use PsCheckout\Infrastructure\Adapter\ContextInterface;
use Psr\Log\LoggerInterface;

class SavePaymentTokenAction implements SavePaymentTokenActionInterface
{
    /**
     * @var PayPalCustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var PayPalOrderRepositoryInterface
     */
    private $payPalOrderRepository;

    /**
     * @var PaymentTokenRepositoryInterface
     */
    private $paymentTokenRepository;

    /**
     * @var ContextInterface
     */
    private $context;

    /**
     * @var ConfigurationInterface
     */
    private $configuration;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        PayPalCustomerRepositoryInterface $customerRepository,
        PayPalOrderRepositoryInterface $payPalOrderRepository,
        PaymentTokenRepositoryInterface $paymentTokenRepository,
        ContextInterface $context,
        ConfigurationInterface $configuration,
        LoggerInterface $logger
    ) {
        $this->customerRepository = $customerRepository;
        $this->payPalOrderRepository = $payPalOrderRepository;
        $this->paymentTokenRepository = $paymentTokenRepository;
        $this->context = $context;
        $this->configuration = $configuration;
        $this->logger = $logger;
    }

    /**
     * {@inheritDoc}
     */
    public function execute(PayPalOrderResponse $payPalOrderResponse)
    {
        if (!$payPalOrderResponse->getVault()) {
            return;
        }

        $this->saveCustomerId($payPalOrderResponse);

        if (!$payPalOrderResponse->getVault()['id']) {
            return;
        }

        $resource = $this->processVault($payPalOrderResponse);
        $this->savePaymentMethodToken($resource);
    }

    /**
     * @param PayPalOrderResponse $orderPayPal
     *
     * @return void
     */
    private function saveCustomerId(PayPalOrderResponse $payPalOrderResponse)
    {
        if (!$payPalOrderResponse->getCustomerId()) {
            return;
        }

        try {
            $this->customerRepository->save((int) $this->context->getCustomer()->id, $payPalOrderResponse->getCustomerId());
        } catch (\Exception $e) {
            $this->logger->error('Failed to save PayPal customer ID.', [
                'exception' => $e,
                'customer_id' => $payPalOrderResponse->getCustomerId(),
            ]);
        }
    }

    /**
     * @param PayPalOrderResponse $payPalOrderResponse
     *
     * @return array
     */
    private function processVault(PayPalOrderResponse $payPalOrderResponse): array
    {
        $resource = $payPalOrderResponse->getVault();
        $resource['metadata'] = ['order_id' => $payPalOrderResponse->getId()];

        $resource['payment_source'] = $payPalOrderResponse->getPaymentSource();
        $resource['payment_source'][$payPalOrderResponse->getFundingSource()]['verification_status'] = $resource['status'];

        return $resource;
    }

    /**
     * @param array $resource
     *
     * @return void
     */
    private function savePaymentMethodToken(array $resource)
    {
        $orderId = $resource['metadata']['order_id'] ?? null;
        $setFavorite = false;

        if ($orderId) {
            try {
                $order = $this->payPalOrderRepository->getOneBy(['id' => $orderId]);
                $setFavorite = in_array(PayPalConfiguration::PS_CHECKOUT_CUSTOMER_INTENT_FAVORITE, $order->getCustomerIntent());
            } catch (\Exception $exception) {
                $this->logger->error('Failed to fetch PayPal order for order ID: ' . $orderId, [
                    'exception' => $exception,
                ]);
            }
        }

        try {
            $token = new PaymentToken(
                $resource['id'],
                $resource['customer']['id'],
                array_keys($resource['payment_source'])[0],
                $resource,
                $this->configuration->get(PayPalConfiguration::PS_CHECKOUT_PAYPAL_ID_MERCHANT),
                $resource['payment_source'][array_keys($resource['payment_source'])[0]]['verification_status'],
                $setFavorite
            );

            $this->paymentTokenRepository->save($token);

            if ($token->isFavorite()) {
                $this->paymentTokenRepository->setTokenFavorite($token->getId(), $token->getPaypalCustomerId());
            }
        } catch (\Exception $exception) {
            $this->logger->error('Failed to save payment token.', [
                'exception' => $exception,
                'resource' => $resource,
            ]);
        }
    }
}
