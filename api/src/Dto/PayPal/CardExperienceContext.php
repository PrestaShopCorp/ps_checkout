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
 * Customizes the payer experience during the 3DS Approval for payment.
 */
class CardExperienceContext
{
    /**
     * @var string|null
     */
    private $returnUrl;

    /**
     * @var string|null
     */
    private $cancelUrl;

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
     */
    public function setReturnUrl(?string $returnUrl): void
    {
        $this->returnUrl = $returnUrl;
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
     */
    public function setCancelUrl(?string $cancelUrl): void
    {
        $this->cancelUrl = $cancelUrl;
    }
}
