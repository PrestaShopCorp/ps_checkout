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

use Exception;
use PsCheckout\Api\Http\CheckoutHttpClientInterface;
use PsCheckout\Core\PaymentToken\Repository\PaymentTokenRepositoryInterface;
use PsCheckout\Core\Settings\Configuration\PayPalConfiguration;
use PsCheckout\Infrastructure\Adapter\ConfigurationInterface;

class DeletePaymentTokenAction implements DeletePaymentTokenActionInterface
{
    /**
     * @var PaymentTokenRepositoryInterface
     */
    private $paymentTokenRepository;

    /**
     * @var CheckoutHttpClientInterface
     */
    private $checkoutHttpClient;

    /**
     * @var ConfigurationInterface
     */
    private $configuration;

    public function __construct(
        PaymentTokenRepositoryInterface $paymentTokenRepository,
        CheckoutHttpClientInterface $checkoutHttpClient,
        ConfigurationInterface $configuration
    ) {
        $this->paymentTokenRepository = $paymentTokenRepository;
        $this->checkoutHttpClient = $checkoutHttpClient;
        $this->configuration = $configuration;
    }

    /**
     * {@inheritDoc}
     */
    public function execute(string $vaultId, int $customerId)
    {
        if (!$this->isTokenOwnedByCustomer($vaultId, $customerId)) {
            throw new Exception('Failed to remove saved payment token');
        }

        $this->paymentTokenRepository->delete($vaultId);
        $this->deletePayPalToken($vaultId);
    }

    /**
     * @param string $vaultId
     * @param int $customerId
     *
     * @return bool
     */
    private function isTokenOwnedByCustomer(string $vaultId, int $customerId): bool
    {
        foreach ($this->paymentTokenRepository->getAllByCustomerId($customerId) as $token) {
            if ($token->getId() === $vaultId) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param string $vaultId
     *
     * @return void
     *
     * @throws Exception
     */
    private function deletePayPalToken(string $vaultId)
    {
        $merchantId = $this->configuration->get(PayPalConfiguration::PS_CHECKOUT_PAYPAL_ID_MERCHANT);

        try {
            $response = $this->checkoutHttpClient->deletePaymentToken($merchantId, $vaultId);

            if ($response->getStatusCode() !== 204) {
                throw new Exception('Failed to delete payment token', $response->getStatusCode());
            }
        } catch (Exception $exception) {
            throw new Exception('Failed to delete payment token', 0, $exception);
        }
    }
}
