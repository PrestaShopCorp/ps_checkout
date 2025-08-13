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

namespace PsCheckout\Presentation\Presenter\Settings\Front\Modules;

use PsCheckout\Core\Settings\Configuration\PayPalConfiguration;
use PsCheckout\Core\Settings\Configuration\PayPalExpressCheckoutConfiguration;
use PsCheckout\Core\Settings\Configuration\PayPalPayLaterConfiguration;
use PsCheckout\Core\Settings\Configuration\PayPalSdkConfiguration;
use PsCheckout\Infrastructure\Adapter\ConfigurationInterface;
use PsCheckout\Infrastructure\Adapter\ContextInterface;
use PsCheckout\Presentation\Presenter\FundingSource\FundingSourcePresenterInterface;
use PsCheckout\Presentation\Presenter\PresenterInterface;

class ConfigurationModule implements PresenterInterface
{
    /**
     * @var string
     */
    private $moduleName;

    /**
     * @var ContextInterface
     */
    private $context;

    /**
     * @var ConfigurationInterface
     */
    private $configuration;

    /**
     * @var PayPalConfiguration
     */
    private $payPalConfiguration;

    /**
     * @var FundingSourcePresenterInterface
     */
    private $fundingSourcePresenter;

    /**
     * @var PayPalSdkConfiguration
     */
    private $payPalSdkConfiguration;

    /**
     * @param string $moduleName
     * @param ContextInterface $context
     * @param ConfigurationInterface $configuration
     * @param PayPalConfiguration $payPalConfiguration
     * @param FundingSourcePresenterInterface $fundingSourcePresenter
     */
    public function __construct(
        string $moduleName,
        ContextInterface $context,
        ConfigurationInterface $configuration,
        PayPalConfiguration $payPalConfiguration,
        FundingSourcePresenterInterface $fundingSourcePresenter,
        PayPalSdkConfiguration $payPalSdkConfiguration
    ) {
        $this->moduleName = $moduleName;
        $this->context = $context;
        $this->configuration = $configuration;
        $this->payPalConfiguration = $payPalConfiguration;
        $this->fundingSourcePresenter = $fundingSourcePresenter;
        $this->payPalSdkConfiguration = $payPalSdkConfiguration;
    }

    public function present(): array
    {
        $isCardAvailable = false;

        foreach ($this->fundingSourcePresenter->getAllActiveForSpecificShop($this->context->getShop()->id) as $fundingSource) {
            if ('card' === $fundingSource->getName()) {
                $isCardAvailable = $fundingSource->getIsEnabled();

                break;
            }
        }

        $isPayPalPaymentsReceivable = $this->configuration->getBoolean(PayPalConfiguration::PS_CHECKOUT_PAYPAL_PAYMENT_STATUS);

        return [
            $this->moduleName . 'PayPalSdkConfig' => $this->payPalSdkConfiguration->buildConfiguration(),
            $this->moduleName . 'PayPalEnvironment' => $this->configuration->get(PayPalConfiguration::PS_CHECKOUT_PAYMENT_MODE),
            $this->moduleName . 'PayPalButtonConfiguration' => $this->configuration->getDeserializedRaw(PayPalConfiguration::PS_CHECKOUT_PAYPAL_BUTTON),
            $this->moduleName . 'AutoRenderDisabled' => $this->configuration->getBoolean('PS_CHECKOUT_AUTO_RENDER_DISABLED'),
            $this->moduleName . 'HostedFieldsEnabled' => $isCardAvailable && $this->configuration->getBoolean(PayPalConfiguration::PS_CHECKOUT_CARD_HOSTED_FIELDS_ENABLED) && in_array($this->configuration->get(PayPalConfiguration::PS_CHECKOUT_CARD_HOSTED_FIELDS_STATUS), ['SUBSCRIBED', 'LIMITED'], true),
            $this->moduleName . 'HostedFieldsContingencies' => $this->payPalConfiguration->getCardFieldsContingencies(),
            $this->moduleName . 'ExpressCheckoutCartEnabled' => $this->configuration->getBoolean(PayPalExpressCheckoutConfiguration::PS_CHECKOUT_EC_ORDER_PAGE) && $isPayPalPaymentsReceivable,
            $this->moduleName . 'ExpressCheckoutOrderEnabled' => $this->configuration->getBoolean(PayPalExpressCheckoutConfiguration::PS_CHECKOUT_EC_CHECKOUT_PAGE) && $isPayPalPaymentsReceivable,
            $this->moduleName . 'ExpressCheckoutProductEnabled' => $this->configuration->getBoolean(PayPalExpressCheckoutConfiguration::PS_CHECKOUT_EC_PRODUCT_PAGE) && $isPayPalPaymentsReceivable,
            $this->moduleName . 'PayLaterOrderPageMessageEnabled' => $this->configuration->getBoolean(PayPalPayLaterConfiguration::PS_CHECKOUT_PAY_LATER_ORDER_PAGE) && $isPayPalPaymentsReceivable,
            $this->moduleName . 'PayLaterProductPageMessageEnabled' => $this->configuration->getBoolean(PayPalPayLaterConfiguration::PS_CHECKOUT_PAY_LATER_PRODUCT_PAGE) && $isPayPalPaymentsReceivable,
            $this->moduleName . 'PayLaterOrderPageBannerEnabled' => $this->configuration->getBoolean(PayPalPayLaterConfiguration::PS_CHECKOUT_PAY_LATER_ORDER_PAGE_BANNER) && $isPayPalPaymentsReceivable,
            $this->moduleName . 'PayLaterHomePageBannerEnabled' => $this->configuration->getBoolean(PayPalPayLaterConfiguration::PS_CHECKOUT_PAY_LATER_HOME_PAGE_BANNER) && $isPayPalPaymentsReceivable,
            $this->moduleName . 'PayLaterCategoryPageBannerEnabled' => $this->configuration->getBoolean(PayPalPayLaterConfiguration::PS_CHECKOUT_PAY_LATER_CATEGORY_PAGE_BANNER) && $isPayPalPaymentsReceivable,
            $this->moduleName . 'PayLaterProductPageBannerEnabled' => $this->configuration->getBoolean(PayPalPayLaterConfiguration::PS_CHECKOUT_PAY_LATER_PRODUCT_PAGE_BANNER) && $isPayPalPaymentsReceivable,
            $this->moduleName . 'PayLaterOrderPageButtonEnabled' => $this->configuration->getBoolean(PayPalPayLaterConfiguration::PS_CHECKOUT_PAY_LATER_ORDER_PAGE_BUTTON) && $isPayPalPaymentsReceivable,
            $this->moduleName . 'PayLaterCartPageButtonEnabled' => $this->configuration->getBoolean(PayPalPayLaterConfiguration::PS_CHECKOUT_PAY_LATER_CART_PAGE_BUTTON) && $isPayPalPaymentsReceivable,
            $this->moduleName . 'PayLaterProductPageButtonEnabled' => $this->configuration->getBoolean(PayPalPayLaterConfiguration::PS_CHECKOUT_PAY_LATER_PRODUCT_PAGE_BUTTON) && $isPayPalPaymentsReceivable,
        ];
    }
}
