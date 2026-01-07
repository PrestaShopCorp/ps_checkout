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

namespace PsCheckout\Api\Dto\PayPal;

/**
 * Customizes the payer experience during the approval process for payment with PayPal. Note: Partners
 * and Marketplaces might configure brand_name and shipping_preference during partner account setup,
 * which overrides the request values.
 */
class PaypalWalletExperienceContext
{
    /**
     * @var string|null
     */
    private $brandName;

    /**
     * @var string|null
     */
    private $locale;

    /**
     * @var value-of<PaypalWalletContextShippingPreference::PREFERENCES>|null
     */
    private $shippingPreference = PaypalWalletContextShippingPreference::GET_FROM_FILE;

    /**
     * @var value-of<PaypalWalletContactPreference::PREFERENCES>|null
     */
    private $contactPreference = PaypalWalletContactPreference::NO_CONTACT_INFO;

    /**
     * @var string|null
     */
    private $returnUrl;

    /**
     * @var string|null
     */
    private $cancelUrl;

    /**
     * @var AppSwitchContext|null
     */
    private $appSwitchContext;

    /**
     * @var value-of<PaypalExperienceLandingPage::PAGES>|null
     */
    private $landingPage = PaypalExperienceLandingPage::NO_PREFERENCE;

    /**
     * @var value-of<PaypalExperienceUserAction::ACTIONS>|null
     */
    private $userAction = PaypalExperienceUserAction::CONTINUE_;

    /**
     * @var value-of<PayeePaymentMethodPreference::PREFERENCES>|null
     */
    private $paymentMethodPreference = PayeePaymentMethodPreference::UNRESTRICTED;

    /**
     * @var CallbackConfiguration|null
     */
    private $orderUpdateCallbackConfig;

    /**
     * Returns Brand Name.
     * The label that overrides the business name in the PayPal account on the PayPal site. The pattern is
     * defined by an external party and supports Unicode.
     */
    public function getBrandName(): ?string
    {
        return $this->brandName;
    }

    /**
     * Sets Brand Name.
     * The label that overrides the business name in the PayPal account on the PayPal site. The pattern is
     * defined by an external party and supports Unicode.
     *
     * @maps brand_name
     * @return self
     */
    public function setBrandName(?string $brandName): self
    {
        $this->brandName = $brandName;

        return $this;
    }

    /**
     * Returns Locale.
     * The [language tag](https://tools.ietf.org/html/bcp47#section-2) for the language in which to
     * localize the error-related strings, such as messages, issues, and suggested actions. The tag is made
     * up of the [ISO 639-2 language code](https://www.loc.gov/standards/iso639-2/php/code_list.php), the
     * optional [ISO-15924 script tag](https://www.unicode.org/iso15924/codelists.html), and the [ISO-3166
     * alpha-2 country code](/api/rest/reference/country-codes/) or [M49 region code](https://unstats.un.
     * org/unsd/methodology/m49/).
     */
    public function getLocale(): ?string
    {
        return $this->locale;
    }

    /**
     * Sets Locale.
     * The [language tag](https://tools.ietf.org/html/bcp47#section-2) for the language in which to
     * localize the error-related strings, such as messages, issues, and suggested actions. The tag is made
     * up of the [ISO 639-2 language code](https://www.loc.gov/standards/iso639-2/php/code_list.php), the
     * optional [ISO-15924 script tag](https://www.unicode.org/iso15924/codelists.html), and the [ISO-3166
     * alpha-2 country code](/api/rest/reference/country-codes/) or [M49 region code](https://unstats.un.
     * org/unsd/methodology/m49/).
     *
     * @maps locale
     * @return self
     */
    public function setLocale(?string $locale): self
    {
        $this->locale = $locale;

        return $this;
    }

    /**
     * Returns Shipping Preference.
     * The location from which the shipping address is derived.
     *
     * @return value-of<PaypalWalletContextShippingPreference::PREFERENCES>|null
     */
    public function getShippingPreference(): ?string
    {
        return $this->shippingPreference;
    }

    /**
     * Sets Shipping Preference.
     * The location from which the shipping address is derived.
     *
     * @maps shipping_preference
     *
     * @param value-of<PaypalWalletContextShippingPreference::PREFERENCES>|null $shippingPreference
     *
     * @return self
     */
    public function setShippingPreference(?string $shippingPreference): self
    {
        $this->shippingPreference = $shippingPreference;

        return $this;
    }

    /**
     * Returns Contact Preference.
     * The preference to display the contact information (buyer’s shipping email & phone number) on
     * PayPal's checkout for easy merchant-buyer communication.
     *
     * @return value-of<PaypalWalletContactPreference::PREFERENCES>|null
     */
    public function getContactPreference(): ?string
    {
        return $this->contactPreference;
    }

    /**
     * Sets Contact Preference.
     * The preference to display the contact information (buyer’s shipping email & phone number) on
     * PayPal's checkout for easy merchant-buyer communication.
     *
     * @maps contact_preference
     *
     * @param value-of<PaypalWalletContactPreference::PREFERENCES>|null $contactPreference
     *
     * @return self
     */
    public function setContactPreference(?string $contactPreference): self
    {
        $this->contactPreference = $contactPreference;

        return $this;
    }

    /**
     * Returns Return Url.
     * Describes the URL.
     */
    public function getReturnUrl(): ?string
    {
        return $this->returnUrl;
    }

    /**
     * Sets Return Url.
     * Describes the URL.
     *
     * @maps return_url
     * @return self
     */
    public function setReturnUrl(?string $returnUrl): self
    {
        $this->returnUrl = $returnUrl;

        return $this;
    }

    /**
     * Returns Cancel Url.
     * Describes the URL.
     */
    public function getCancelUrl(): ?string
    {
        return $this->cancelUrl;
    }

    /**
     * Sets Cancel Url.
     * Describes the URL.
     *
     * @maps cancel_url
     * @return self
     */
    public function setCancelUrl(?string $cancelUrl): self
    {
        $this->cancelUrl = $cancelUrl;

        return $this;
    }

    /**
     * Returns App Switch Context.
     * Merchant provided details of the native app or mobile web browser to facilitate buyer's app switch
     * to the PayPal consumer app.
     */
    public function getAppSwitchContext(): ?AppSwitchContext
    {
        return $this->appSwitchContext;
    }

    /**
     * Sets App Switch Context.
     * Merchant provided details of the native app or mobile web browser to facilitate buyer's app switch
     * to the PayPal consumer app.
     *
     * @maps app_switch_context
     * @return self
     */
    public function setAppSwitchContext(?AppSwitchContext $appSwitchContext): self
    {
        $this->appSwitchContext = $appSwitchContext;

        return $this;
    }

    /**
     * Returns Landing Page.
     * The type of landing page to show on the PayPal site for customer checkout.
     *
     * @return value-of<PaypalExperienceLandingPage::PAGES>|null
     */
    public function getLandingPage(): ?string
    {
        return $this->landingPage;
    }

    /**
     * Sets Landing Page.
     * The type of landing page to show on the PayPal site for customer checkout.
     *
     * @maps landing_page
     *
     * @param value-of<PaypalExperienceLandingPage::PAGES>|null $landingPage
     *
     * @return self
     */
    public function setLandingPage(?string $landingPage): self
    {
        $this->landingPage = $landingPage;

        return $this;
    }

    /**
     * Returns User Action.
     * Configures a Continue or Pay Now checkout flow.
     *
     * @return value-of<PaypalExperienceUserAction::ACTIONS>|null
     */
    public function getUserAction(): ?string
    {
        return $this->userAction;
    }

    /**
     * Sets User Action.
     * Configures a Continue or Pay Now checkout flow.
     *
     * @maps user_action
     *
     * @param value-of<PaypalExperienceUserAction::ACTIONS>|null $userAction
     *
     * @return self
     */
    public function setUserAction(?string $userAction): self
    {
        $this->userAction = $userAction;

        return $this;
    }

    /**
     * Returns Payment Method Preference.
     * The merchant-preferred payment methods.
     *
     * @return value-of<PayeePaymentMethodPreference::PREFERENCES>|null
     */
    public function getPaymentMethodPreference(): ?string
    {
        return $this->paymentMethodPreference;
    }

    /**
     * Sets Payment Method Preference.
     * The merchant-preferred payment methods.
     *
     * @maps payment_method_preference
     *
     * @param value-of<PayeePaymentMethodPreference::PREFERENCES>|null $paymentMethodPreference
     *
     * @return self
     */
    public function setPaymentMethodPreference(?string $paymentMethodPreference): self
    {
        $this->paymentMethodPreference = $paymentMethodPreference;

        return $this;
    }

    /**
     * Returns Order Update Callback Config.
     * CallBack Configuration that the merchant can provide to PayPal/Venmo.
     */
    public function getOrderUpdateCallbackConfig(): ?CallbackConfiguration
    {
        return $this->orderUpdateCallbackConfig;
    }

    /**
     * Sets Order Update Callback Config.
     * CallBack Configuration that the merchant can provide to PayPal/Venmo.
     *
     * @maps order_update_callback_config
     * @return self
     */
    public function setOrderUpdateCallbackConfig(?CallbackConfiguration $orderUpdateCallbackConfig): self
    {
        $this->orderUpdateCallbackConfig = $orderUpdateCallbackConfig;

        return $this;
    }
}
