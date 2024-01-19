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

namespace PrestaShop\Module\PrestashopCheckout\PayPal\Order\ValueObject;

class PayPalOrderPaymentSource
{
    /**
     * @var string
     */
    private $name;
    /**
     * @var string
     */
    private $referenceId;
    /**
     * @var string
     */
    private $paymentMethodPreference;
    /**
     * @var string
     */
    private $paymentMethodSelected;
    /**
     * @var string
     */
    private $brandName;
    /**
     * @var string
     */
    private $locale;
    /**
     * @var string
     */
    private $landingPage;
    /**
     * @var string
     */
    private $userAction;
    /**
     * @var string
     */
    private $returnUrl;
    /**
     * @var string
     */
    private $cancelUrl;

    /**
     * @param string $name
     * @param string $referenceId
     * @param string $paymentMethodPreference
     * @param string $paymentMethodSelected
     * @param string $brandName
     * @param string $locale
     * @param string $landingPage
     * @param string $userAction
     * @param string $returnUrl
     * @param string $cancelUrl
     */
    public function __construct($name, $referenceId, $paymentMethodPreference = null, $paymentMethodSelected = null, $brandName = null, $locale = null, $landingPage = null, $userAction = null, $returnUrl = null, $cancelUrl = null)
    {
        $this->name = $name;
        $this->referenceId = $referenceId;
        $this->paymentMethodPreference = $paymentMethodPreference;
        $this->paymentMethodSelected = $paymentMethodSelected;
        $this->brandName = $brandName;
        $this->locale = $locale;
        $this->landingPage = $landingPage;
        $this->userAction = $userAction;
        $this->returnUrl = $returnUrl;
        $this->cancelUrl = $cancelUrl;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getPaymentMethodPreference()
    {
        return $this->paymentMethodPreference;
    }

    /**
     * @param string $paymentMethodPreference
     */
    public function setPaymentMethodPreference($paymentMethodPreference)
    {
        $this->paymentMethodPreference = $paymentMethodPreference;
    }

    /**
     * @return string
     */
    public function getPaymentMethodSelected()
    {
        return $this->paymentMethodSelected;
    }

    /**
     * @param string $paymentMethodSelected
     */
    public function setPaymentMethodSelected($paymentMethodSelected)
    {
        $this->paymentMethodSelected = $paymentMethodSelected;
    }

    /**
     * @return string
     */
    public function getBrandName()
    {
        return $this->brandName;
    }

    /**
     * @param string $brandName
     */
    public function setBrandName($brandName)
    {
        $this->brandName = $brandName;
    }

    /**
     * @return string
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * @param string $locale
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;
    }

    /**
     * @return string
     */
    public function getLandingPage()
    {
        return $this->landingPage;
    }

    /**
     * @param string $landingPage
     */
    public function setLandingPage($landingPage)
    {
        $this->landingPage = $landingPage;
    }

    /**
     * @return string
     */
    public function getUserAction()
    {
        return $this->userAction;
    }

    /**
     * @param string $userAction
     */
    public function setUserAction($userAction)
    {
        $this->userAction = $userAction;
    }

    /**
     * @return string
     */
    public function getReturnUrl()
    {
        return $this->returnUrl;
    }

    /**
     * @param string $returnUrl
     */
    public function setReturnUrl($returnUrl)
    {
        $this->returnUrl = $returnUrl;
    }

    /**
     * @return string
     */
    public function getCancelUrl()
    {
        return $this->cancelUrl;
    }

    /**
     * @param string $cancelUrl
     */
    public function setCancelUrl($cancelUrl)
    {
        $this->cancelUrl = $cancelUrl;
    }

    public function toArray()
    {
        return [
            $this->name => [
                'experience_context' => [
                    'payment_method_preference' => $this->paymentMethodPreference,
                    'payment_method_selected' => $this->paymentMethodSelected,
                    'brand_name' => $this->brandName,
                    'locale' => $this->locale,
                    'landing_page' => $this->landingPage,
                    'user_action' => $this->userAction,
                    'return_url' => $this->returnUrl,
                    'cancel_url' => $this->cancelUrl,
                ],
            ],
        ];
    }
}
