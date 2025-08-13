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

namespace PsCheckout\Core\PayPal\Card3DSecure;

use PsCheckout\Api\ValueObject\PayPalOrderResponse;

class Card3DSecureValidator implements Card3DSecureValidatorInterface
{
    /**
     * {@inheritDoc}
     */
    public function getAuthorizationDecision(PayPalOrderResponse $payPalOrderResponse): int
    {
        $authResult = $payPalOrderResponse->getAuthenticationResult();

        if (!$authResult) {
            return Card3DSecureConfiguration::DECISION_NO_DECISION;
        }

        $liabilityShift = $payPalOrderResponse->getLiabilityShift();

        if ($liabilityShift === Card3DSecureConfiguration::LIABILITY_SHIFT_POSSIBLE) {
            return Card3DSecureConfiguration::DECISION_PROCEED;
        }

        if ($liabilityShift === Card3DSecureConfiguration::LIABILITY_SHIFT_UNKNOWN) {
            return Card3DSecureConfiguration::DECISION_RETRY;
        }

        if ($liabilityShift === Card3DSecureConfiguration::LIABILITY_SHIFT_NO && ($threeDSecure = $payPalOrderResponse->get3dSecure())) {
            return $this->handleNoLiabilityShift($threeDSecure);
        }

        return Card3DSecureConfiguration::DECISION_NO_DECISION;
    }

    /**
     * {@inheritDoc}
     */
    public function is3DSecureAvailable(PayPalOrderResponse $payPalOrderResponse): bool
    {
        $enrollmentStatus = $payPalOrderResponse->get3dSecureEnrollmentStatus();

        return in_array($enrollmentStatus, [Card3DSecureConfiguration::ENROLLMENT_STATUS_YES, Card3DSecureConfiguration::ENROLLMENT_STATUS_UNAVAILABLE]);
    }

    /**
     * {@inheritDoc}
     */
    public function isLiabilityShifted(PayPalOrderResponse $payPalOrderResponse): bool
    {
        $liabilityShift = $payPalOrderResponse->getLiabilityShift();
        $authenticationStatus = $payPalOrderResponse->get3dSecureAuthenticationStatus();

        return in_array($liabilityShift, [Card3DSecureConfiguration::LIABILITY_SHIFT_YES, Card3DSecureConfiguration::LIABILITY_SHIFT_POSSIBLE])
            && $authenticationStatus === Card3DSecureConfiguration::AUTH_RESULT_YES;
    }

    /**
     * Handles the decision when no liability shift occurs.
     *
     * @param array $threeDSecure
     *
     * @return int
     */
    private function handleNoLiabilityShift(array $threeDSecure): int
    {
        $enrollmentStatus = $threeDSecure['enrollment_status'] ?? null;
        $authStatus = $threeDSecure['authentication_status'] ?? null;

        if (in_array($enrollmentStatus, [Card3DSecureConfiguration::ENROLLMENT_STATUS_BYPASS, Card3DSecureConfiguration::ENROLLMENT_STATUS_UNAVAILABLE, Card3DSecureConfiguration::ENROLLMENT_STATUS_NO], true) && !$authStatus) {
            return Card3DSecureConfiguration::DECISION_PROCEED;
        }

        if (in_array($authStatus, [Card3DSecureConfiguration::AUTH_RESULT_REJECTED, Card3DSecureConfiguration::AUTH_RESULT_NO], true)) {
            return Card3DSecureConfiguration::DECISION_REJECT;
        }

        if ($authStatus === Card3DSecureConfiguration::AUTH_RESULT_UNABLE || !$authStatus) {
            return Card3DSecureConfiguration::DECISION_RETRY;
        }

        return Card3DSecureConfiguration::DECISION_NO_DECISION;
    }
}
