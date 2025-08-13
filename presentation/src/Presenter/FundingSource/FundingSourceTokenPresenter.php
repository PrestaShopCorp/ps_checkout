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

namespace PsCheckout\Presentation\Presenter\FundingSource;

use PsCheckout\Core\FundingSource\Factory\FundingSourceTokenFactoryInterface;
use PsCheckout\Core\PaymentToken\Repository\PaymentTokenRepositoryInterface;
use PsCheckout\Core\PaymentToken\ValueObject\PaymentToken;
use PsCheckout\Core\Settings\Configuration\PayPalConfiguration;
use PsCheckout\Infrastructure\Adapter\ConfigurationInterface;

class FundingSourceTokenPresenter implements FundingSourceTokenPresenterInterface
{
    /**
     * @var PaymentTokenRepositoryInterface
     */
    private $paymentTokenRepository;

    /**
     * @var ConfigurationInterface
     */
    private $configuration;

    /**
     * @var FundingSourceTokenFactoryInterface
     */
    private $fundingSourceTokenFactory;

    public function __construct(PaymentTokenRepositoryInterface $paymentTokenRepository, ConfigurationInterface $configuration, FundingSourceTokenFactoryInterface $fundingSourceTokenFactory)
    {
        $this->paymentTokenRepository = $paymentTokenRepository;
        $this->fundingSourceTokenFactory = $fundingSourceTokenFactory;
        $this->configuration = $configuration;
    }

    /**
     * {@inheritdoc}
     */
    public function getFundingSourceTokens(int $customerId): array
    {
        if (!$customerId) {
            return [];
        }

        $paymentTokens = $this->paymentTokenRepository->findVaultedTokensByCustomerAndMerchant(
            $customerId,
            $this->configuration->get(PayPalConfiguration::PS_CHECKOUT_PAYPAL_ID_MERCHANT)
        );

        $fundingSourceTokens = [];

        foreach ($paymentTokens as $tokenData) {
            $paymentToken = PaymentToken::createFromArray($tokenData);
            $fundingSourceTokens[] = $this->fundingSourceTokenFactory->createFromPaymentToken($paymentToken);
        }

        return $fundingSourceTokens;
    }
}
