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
 * Customizes the buyer experience during the approval process for payment with Venmo. Note: Partners
 * and Marketplaces might configure shipping_preference during partner account setup, which overrides
 * the request values.
 */
class VenmoWalletExperienceContext
{
    /**
     * @var string|null
     */
    private $brandName;

    /**
     * @var string|null
     */
    private $shippingPreference = VenmoWalletExperienceContextShippingPreference::GET_FROM_FILE;

    /**
     * @var CallbackConfiguration|null
     */
    private $orderUpdateCallbackConfig;

    /**
     * @var string|null
     */
    private $userAction = VenmoWalletExperienceContextUserAction::CONTINUE_;

    /**
     * Returns Brand Name.
     * The business name of the merchant. The pattern is defined by an external party and supports Unicode.
     */
    public function getBrandName(): ?string
    {
        return $this->brandName;
    }

    /**
     * Sets Brand Name.
     * The business name of the merchant. The pattern is defined by an external party and supports Unicode.
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
     * Returns Shipping Preference.
     * The location from which the shipping address is derived.
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
     * @return self
     */
    public function setShippingPreference(?string $shippingPreference): self
    {
        $this->shippingPreference = $shippingPreference;

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

    /**
     * Returns User Action.
     * Configures a Continue or Pay Now checkout flow.
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
     * @return self
     */
    public function setUserAction(?string $userAction): self
    {
        $this->userAction = $userAction;

        return $this;
    }
}
