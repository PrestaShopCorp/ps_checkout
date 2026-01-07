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
 * CallBack Configuration that the merchant can provide to PayPal/Venmo.
 */
class CallbackConfiguration
{
    /**
     * @var string[]
     */
    private $callbackEvents;

    /**
     * @var string
     */
    private $callbackUrl;

    /**
     * @param string[] $callbackEvents
     * @param string $callbackUrl
     */
    public function __construct(array $callbackEvents, string $callbackUrl)
    {
        $this->callbackEvents = $callbackEvents;
        $this->callbackUrl = $callbackUrl;
    }

    /**
     * Returns Callback Events.
     * An array of callback events merchant can subscribe to for the corresponding callback url.
     *
     * @return string[]
     */
    public function getCallbackEvents(): array
    {
        return $this->callbackEvents;
    }

    /**
     * Sets Callback Events.
     * An array of callback events merchant can subscribe to for the corresponding callback url.
     *
     * @required
     * @maps callback_events
     *
     * @param string[] $callbackEvents
     */
    public function setCallbackEvents(array $callbackEvents): void
    {
        $this->callbackEvents = $callbackEvents;
    }

    /**
     * Returns Callback Url.
     * Merchant provided CallBack url.PayPal/Venmo will use this url to call the merchant back when the
     * events occur .PayPal/Venmo expects a secured url usually in the https format.merchant can append the
     * cart id or other params part of the url as query or path params.
     */
    public function getCallbackUrl(): string
    {
        return $this->callbackUrl;
    }

    /**
     * Sets Callback Url.
     * Merchant provided CallBack url.PayPal/Venmo will use this url to call the merchant back when the
     * events occur .PayPal/Venmo expects a secured url usually in the https format.merchant can append the
     * cart id or other params part of the url as query or path params.
     *
     * @required
     * @maps callback_url
     */
    public function setCallbackUrl(string $callbackUrl): void
    {
        $this->callbackUrl = $callbackUrl;
    }
}
