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

namespace PsCheckout\Presentation\Presenter\Settings\Admin\Modules;

use PsCheckout\Core\Settings\Configuration\PayPalConfiguration;
use PsCheckout\Infrastructure\Adapter\ConfigurationInterface;
use PsCheckout\Presentation\Presenter\PresenterInterface;

/**
 * Construct the PayPal module
 */
class PaypalModule implements PresenterInterface
{
    /**
     * @var ConfigurationInterface
     */
    private $configuration;

    /**
     * @param ConfigurationInterface $configuration
     */
    public function __construct(ConfigurationInterface $configuration)
    {
        $this->configuration = $configuration;
    }

    /**
     * Present the paypal module (vuex)
     *
     * @return array
     */
    public function present(): array
    {
        return [
            'paypal' => [
                'idMerchant' => $this->configuration->get(PayPalConfiguration::PS_CHECKOUT_PAYPAL_ID_MERCHANT),
                'paypalIsActive' => $this->configuration->getBoolean(PayPalConfiguration::PS_CHECKOUT_PAYPAL_PAYMENT_STATUS),
            ],
        ];
    }
}
