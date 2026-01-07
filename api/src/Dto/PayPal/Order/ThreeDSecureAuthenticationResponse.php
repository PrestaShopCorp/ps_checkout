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

namespace PsCheckout\Api\Dto\PayPal\Order;

/**
 * Results of 3D Secure Authentication.
 */
class ThreeDSecureAuthenticationResponse
{
    /**
     * @var string|null
     */
    private $authenticationStatus;

    /**
     * @var string|null
     */
    private $enrollmentStatus;

    /**
     * Returns Authentication Status.
     * Transactions status result identifier. The outcome of the issuer's authentication.
     */
    public function getAuthenticationStatus(): ?string
    {
        return $this->authenticationStatus;
    }

    /**
     * Sets Authentication Status.
     * Transactions status result identifier. The outcome of the issuer's authentication.
     *
     * @maps authentication_status
     */
    public function setAuthenticationStatus(?string $authenticationStatus): void
    {
        $this->authenticationStatus = $authenticationStatus;
    }

    /**
     * Returns Enrollment Status.
     * Status of Authentication eligibility.
     */
    public function getEnrollmentStatus(): ?string
    {
        return $this->enrollmentStatus;
    }

    /**
     * Sets Enrollment Status.
     * Status of Authentication eligibility.
     *
     * @maps enrollment_status
     */
    public function setEnrollmentStatus(?string $enrollmentStatus): void
    {
        $this->enrollmentStatus = $enrollmentStatus;
    }
}
