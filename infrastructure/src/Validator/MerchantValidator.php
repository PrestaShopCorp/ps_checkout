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

namespace PsCheckout\Infrastructure\Validator;

use PsCheckout\Core\Settings\Configuration\PayPalConfiguration;
use PsCheckout\Infrastructure\Adapter\ConfigurationInterface;
use PsCheckout\Infrastructure\Repository\PsAccountRepositoryInterface;

class MerchantValidator implements MerchantValidatorInterface
{
    /**
     * @var ConfigurationInterface
     */
    private $configuration;

    /**
     * @var PsAccountRepositoryInterface
     */
    private $psAccountRepository;

    public function __construct(
        ConfigurationInterface $configuration,
        PsAccountRepositoryInterface $psAccountRepository
    ) {
        $this->configuration = $configuration;
        $this->psAccountRepository = $psAccountRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function isValid(): bool
    {
        return $this->configuration->get(PayPalConfiguration::PS_CHECKOUT_PAYPAL_ID_MERCHANT)
            && $this->configuration->getBoolean(PayPalConfiguration::PS_CHECKOUT_PAYPAL_EMAIL_STATUS)
            && $this->configuration->getBoolean(PayPalConfiguration::PS_CHECKOUT_PAYPAL_PAYMENT_STATUS)
            && $this->psAccountRepository->isAccountLinked();
    }
}
