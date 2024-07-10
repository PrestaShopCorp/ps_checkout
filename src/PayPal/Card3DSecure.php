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

namespace PrestaShop\Module\PrestashopCheckout\PayPal;

/**
 * Parameters related to 3D Secure 2.0
 * Recommended action based on `enrollment_status` and `authentication_status` parameters, a `liability_shift` determines how you might proceed with authentication
 *
 * @see https://developer.paypal.com/docs/checkout/advanced/customize/3d-secure/response-parameters/#enrollmentstatusauthentication_statusliabilityshiftrecommended-action
 */
class Card3DSecure
{
    // Liability has shifted to the card issuer. Available only after order is authorized or captured.
    const LIABILITY_SHIFT_YES = 'YES';
    // Liability is with the merchant.
    const LIABILITY_SHIFT_NO = 'NO';
    // Liability may shift to the card issuer. Available only before order is authorized or captured.
    const LIABILITY_SHIFT_POSSIBLE = 'POSSIBLE';
    // The authentication system is not available.
    const LIABILITY_SHIFT_UNKNOWN = 'UNKNOWN';

    // Yes. The bank is participating in 3-D Secure protocol and will return the ACSUrl.
    const ENROLLMENT_STATUS_YES = 'Y';
    // No. The bank is not participating in 3-D Secure protocol.
    const ENROLLMENT_STATUS_NO = 'N';
    // Unavailable. The DS or ACS is not available for authentication at the time of the request.
    const ENROLLMENT_STATUS_UNAVAILABLE = 'U';
    // Bypass. The merchant authentication rule is triggered to bypass authentication.
    const ENROLLMENT_STATUS_BYPASS = 'B';

    // Successful authentication.
    const AUTHENTICATION_RESULT_YES = 'Y';
    // Failed authentication / account not verified / transaction denied.
    const AUTHENTICATION_RESULT_NO = 'N';
    // Unable to complete authentication.
    const AUTHENTICATION_RESULT_UNABLE = 'U';
    // Authentication rejected (merchant must not submit for authorization).
    const AUTHENTICATION_RESULT_REJECTED = 'R';
    // Successful attempts transaction.
    const AUTHENTICATION_RESULT_ATTEMPTED = 'A';
    // Challenge required for authentication.
    const AUTHENTICATION_RESULT_CHALLENGE_REQUIRED = 'C';
    // Challenge required; decoupled authentication confirmed.
    const AUTHENTICATION_RESULT_DECOUPLED = 'D';
    // Informational only; 3DS requester challenge preference acknowledged.
    const AUTHENTICATION_RESULT_INFO = 'I';

    // Continue with authorization at your own risk, meaning that the liability of any chargeback has not shifted from the merchant to the card issuer.
    const NO_DECISION = 0;
    // Continue with authorization.
    const PROCEED = 1;
    // Do not continue with authorization.
    const REJECT = 2;
    // Do not continue with authorization. Request cardholder to retry.
    const RETRY = 3;

    /**
     * @param array $order
     *
     * @return int
     */
    public function continueWithAuthorization(array $order)
    {
        $cardAuthenticationResult = $this->getAuthenticationResult($order);

        if (!$cardAuthenticationResult) {
            return static::NO_DECISION;
        }

        $liabilityShift = $this->getLiabilityShift($cardAuthenticationResult);

        if ($liabilityShift === static::LIABILITY_SHIFT_POSSIBLE) {
            return static::PROCEED;
        }

        if ($liabilityShift === static::LIABILITY_SHIFT_UNKNOWN) {
            return static::RETRY;
        }

        $threeDSecure = $this->get3DSecure($cardAuthenticationResult);

        if ($liabilityShift === static::LIABILITY_SHIFT_NO && $threeDSecure) {
            return $this->noLiabilityShift($cardAuthenticationResult);
        }

        return static::NO_DECISION;
    }

    /**
     * @param array $order
     *
     * @return bool
     */
    public function is3DSecureAvailable(array $order)
    {
        $cardAuthenticationResult = $this->getAuthenticationResult($order);

        if (!$cardAuthenticationResult) {
            return false;
        }

        $threeDSecure = $this->get3DSecure($cardAuthenticationResult);
        $enrollmentStatus = $this->getEnrollmentStatus($threeDSecure);

        return $enrollmentStatus === self::ENROLLMENT_STATUS_YES || $enrollmentStatus === self::ENROLLMENT_STATUS_UNAVAILABLE;
    }

    /**
     * @param array $order
     *
     * @return bool
     */
    public function isLiabilityShifted(array $order)
    {
        $authenticationResult = $this->getAuthenticationResult($order);
        $liabilityShift = $this->getLiabilityShift($authenticationResult);
        $threeDSecure = $this->get3DSecure($authenticationResult);
        $authenticationStatus = $this->getAuthenticationStatus($threeDSecure);

        return ($liabilityShift === self::LIABILITY_SHIFT_POSSIBLE || $liabilityShift === self::LIABILITY_SHIFT_YES)
            && $authenticationStatus === self::AUTHENTICATION_RESULT_YES;
    }

    /**
     * @param array{enrollment_status: string, authentication_status: string} $cardAuthenticationResult
     *
     * @return int
     */
    private function noLiabilityShift(array $cardAuthenticationResult)
    {
        $threeDSecure = $this->get3DSecure($cardAuthenticationResult);
        $enrollmentStatus = $this->getEnrollmentStatus($threeDSecure);
        $authenticationStatus = $this->getAuthenticationStatus($threeDSecure);

        if ($enrollmentStatus === static::ENROLLMENT_STATUS_BYPASS && !$authenticationStatus) {
            return static::PROCEED;
        }

        if ($enrollmentStatus === static::ENROLLMENT_STATUS_UNAVAILABLE && !$authenticationStatus) {
            return static::PROCEED;
        }

        if ($enrollmentStatus === static::ENROLLMENT_STATUS_NO && !$authenticationStatus) {
            return static::PROCEED;
        }

        if ($authenticationStatus === static::AUTHENTICATION_RESULT_REJECTED) {
            return static::REJECT;
        }

        if ($authenticationStatus === static::AUTHENTICATION_RESULT_NO) {
            return static::REJECT;
        }

        if ($authenticationStatus === static::AUTHENTICATION_RESULT_UNABLE) {
            return static::RETRY;
        }

        if (!$authenticationStatus) {
            return static::RETRY;
        }

        return static::NO_DECISION;
    }

    /**
     * @param array $order
     *
     * @return array|null
     */
    private function getAuthenticationResult(array $order)
    {
        $fundingSource = isset($order['payment_source']) ? key($order['payment_source']) : '';

        return isset($order['payment_source'][$fundingSource]['authentication_result']) ? $order['payment_source'][$fundingSource]['authentication_result'] : null;
    }

    /**
     * @param array|null $authenticationResult
     *
     * @return string|null
     */
    private function getLiabilityShift($authenticationResult)
    {
        return isset($authenticationResult['liability_shift']) ? $authenticationResult['liability_shift'] : null;
    }

    /**
     * @param array|null $authenticationResult
     *
     * @return array|null
     */
    private function get3DSecure($authenticationResult)
    {
        return isset($authenticationResult['three_d_secure']) ? $authenticationResult['three_d_secure'] : null;
    }

    /**
     * @param array|null $threeDSecure
     *
     * @return string|null
     */
    public function getAuthenticationStatus($threeDSecure)
    {
        return isset($threeDSecure['authentication_status']) ? $threeDSecure['authentication_status'] : null;
    }

    /**
     * @param array|null $threeDSecure
     *
     * @return string|null
     */
    private function getEnrollmentStatus($threeDSecure)
    {
        return isset($threeDSecure['enrollment_status']) ? $threeDSecure['enrollment_status'] : null;
    }
}
