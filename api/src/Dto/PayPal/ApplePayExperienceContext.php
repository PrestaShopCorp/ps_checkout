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
 * Customizes the payer experience during the approval process for the payment.
 */
class ApplePayExperienceContext
{
    /**
     * @var string
     */
    private $returnUrl;

    /**
     * @var string
     */
    private $cancelUrl;

    /**
     * @param string $returnUrl
     * @param string $cancelUrl
     */
    public function __construct(string $returnUrl, string $cancelUrl)
    {
        $this->returnUrl = $returnUrl;
        $this->cancelUrl = $cancelUrl;
    }

    /**
     * Returns Return Url.
     * Describes the URL.
     */
    public function getReturnUrl(): string
    {
        return $this->returnUrl;
    }

    /**
     * Sets Return Url.
     * Describes the URL.
     *
     * @required
     * @maps return_url
     * @return self
     */
    public function setReturnUrl(string $returnUrl): self
    {
        $this->returnUrl = $returnUrl;

        return $this;
    }

    /**
     * Returns Cancel Url.
     * Describes the URL.
     */
    public function getCancelUrl(): string
    {
        return $this->cancelUrl;
    }

    /**
     * Sets Cancel Url.
     * Describes the URL.
     *
     * @required
     * @maps cancel_url
     * @return self
     */
    public function setCancelUrl(string $cancelUrl): self
    {
        $this->cancelUrl = $cancelUrl;

        return $this;
    }
}
