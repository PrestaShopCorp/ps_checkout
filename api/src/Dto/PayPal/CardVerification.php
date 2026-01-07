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
 * The API caller can opt in to verify the card through PayPal offered verification services (e.g.
 * Smart Dollar Auth, 3DS).
 */
class CardVerification
{
    /**
     * @var string|null
     */
    private $method = OrdersCardVerificationMethod::SCA_WHEN_REQUIRED;

    /**
     * Returns Method.
     * The method used for card verification.
     */
    public function getMethod(): ?string
    {
        return $this->method;
    }

    /**
     * Sets Method.
     * The method used for card verification.
     *
     * @maps method
     * @return self
     */
    public function setMethod(?string $method): self
    {
        $this->method = $method;

        return $this;
    }
}
