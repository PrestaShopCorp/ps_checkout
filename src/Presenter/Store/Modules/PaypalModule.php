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

namespace PrestaShop\Module\PrestashopCheckout\Presenter\Store\Modules;

use PrestaShop\Module\PrestashopCheckout\PayPal\PayPalConfiguration;
use PrestaShop\Module\PrestashopCheckout\Presenter\PresenterInterface;

/**
 * Construct the PayPal module
 */
class PaypalModule implements PresenterInterface
{
    /**
     * @var PayPalConfiguration
     */
    private $configuration;

    /**
     * @param PayPalConfiguration $configuration
     */
    public function __construct(PayPalConfiguration $configuration)
    {
        $this->configuration = $configuration;
    }

    /**
     * Present the paypal module (vuex)
     *
     * @return array
     */
    public function present()
    {
        return [
            'paypal' => [
                'idMerchant' => $this->configuration->getMerchantId(),
                'paypalOnboardingLink' => '',
                'onboardingCompleted' => !empty($this->configuration->getMerchantId()),
                'accountIslinked' => !empty($this->configuration->getMerchantEmail()) && !empty($this->configuration->getMerchantId()),
                'emailMerchant' => $this->configuration->getMerchantEmail(),
                'emailIsValid' => $this->configuration->isMerchantEmailConfirmed(),
                'cardIsActive' => $this->configuration->getCardHostedFieldsStatus(),
                'paypalIsActive' => $this->configuration->isPayPalPaymentsReceivable(),
                'countryMerchant' => $this->configuration->getMerchantCountry(),
            ],
        ];
    }
}
