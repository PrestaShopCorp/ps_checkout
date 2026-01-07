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
 * Results of Authentication such as 3D Secure.
 */
class AuthenticationResponse
{
    /**
     * @var string|null
     */
    private $liabilityShift;

    /**
     * @var ThreeDSecureAuthenticationResponse|null
     */
    private $threeDSecure;

    /**
     * Returns Liability Shift.
     * Liability shift indicator. The outcome of the issuer's authentication.
     */
    public function getLiabilityShift(): ?string
    {
        return $this->liabilityShift;
    }

    /**
     * Sets Liability Shift.
     * Liability shift indicator. The outcome of the issuer's authentication.
     *
     * @maps liability_shift
     * @return self
     */
    public function setLiabilityShift(?string $liabilityShift): self
    {
        $this->liabilityShift = $liabilityShift;

        return $this;
    }

    /**
     * Returns Three D Secure.
     * Results of 3D Secure Authentication.
     */
    public function getThreeDSecure(): ?ThreeDSecureAuthenticationResponse
    {
        return $this->threeDSecure;
    }

    /**
     * Sets Three D Secure.
     * Results of 3D Secure Authentication.
     *
     * @maps three_d_secure
     * @return self
     */
    public function setThreeDSecure(?ThreeDSecureAuthenticationResponse $threeDSecure): self
    {
        $this->threeDSecure = $threeDSecure;

        return $this;
    }
}
