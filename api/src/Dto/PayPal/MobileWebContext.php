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
 * Buyer's mobile web browser context to app switch to the PayPal consumer app.
 */
class MobileWebContext
{
    /**
     * @var string|null
     */
    private $returnFlow = MobileReturnFlow::AUTO;

    /**
     * @var string|null
     */
    private $buyerUserAgent;

    /**
     * Returns Return Flow.
     * Merchant preference on how the buyer can navigate back to merchant website post approving the
     * transaction on the PayPal App.
     */
    public function getReturnFlow(): ?string
    {
        return $this->returnFlow;
    }

    /**
     * Sets Return Flow.
     * Merchant preference on how the buyer can navigate back to merchant website post approving the
     * transaction on the PayPal App.
     *
     * @maps return_flow
     * @return self
     */
    public function setReturnFlow(?string $returnFlow): self
    {
        $this->returnFlow = $returnFlow;

        return $this;
    }

    /**
     * Returns Buyer User Agent.
     * User agent from the request originating from the buyer's device. This will be used to identify the
     * buyer's operating system and browser versions. NOTE: Merchants must not alter or modify the buyer's
     * device user agent.
     */
    public function getBuyerUserAgent(): ?string
    {
        return $this->buyerUserAgent;
    }

    /**
     * Sets Buyer User Agent.
     * User agent from the request originating from the buyer's device. This will be used to identify the
     * buyer's operating system and browser versions. NOTE: Merchants must not alter or modify the buyer's
     * device user agent.
     *
     * @maps buyer_user_agent
     * @return self
     */
    public function setBuyerUserAgent(?string $buyerUserAgent): self
    {
        $this->buyerUserAgent = $buyerUserAgent;

        return $this;
    }
}
