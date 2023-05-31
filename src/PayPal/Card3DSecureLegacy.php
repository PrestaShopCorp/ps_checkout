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
 * Deprecated parameters related to 3D Secure 1.0
 * Recommended action based on `AuthenticationReason` and `authenticationStatus` parameters, a `liabilityShifted` determines how you might proceed with authentication
 *
 * @see https://developer.paypal.com/docs/checkout/advanced/customize/3d-secure/response-parameters/#link-deprecatedparameters
 */
class Card3DSecureLegacy
{
    // Liability has shifted to the card issuer. Available only after order is authorized or captured.
    const LIABILITY_SHIFT_YES = 'YES';
    // Liability is with the merchant.
    const LIABILITY_SHIFT_NO = 'NO';
    // Liability may shift to the card issuer. Available only before order is authorized or captured.
    const LIABILITY_SHIFT_POSSIBLE = 'POSSIBLE';
    // The authentication system is not available.
    const LIABILITY_SHIFT_UNKNOWN = 'UNKNOWN';

    // Continue with authorization at your own risk, meaning that the liability of any chargeback has not shifted from the merchant to the card issuer.
    const NO_DECISION = 0;
    // Continue with authorization.
    const PROCEED = 1;
    // Do not continue with authorization.
    const REJECT = 2;
    // Do not continue with authorization. Request cardholder to retry.
    const RETRY = 3;

    /**
     * @param array{liabilityShift: string|null, liabilityShifted: bool|null, authenticationStatus: string|null, authenticationReason: string|null} $payload
     *
     * @return int
     */
    public function continueWithAuthorization(array $payload)
    {
        // We still use deprecated parameters cause there no equivalent to manage all scenarios, PayPal ensure it still usable.
        $liabilityShift = isset($payload['liabilityShift']) ? $payload['liabilityShift'] : null;
        $liabilityShifted = isset($payload['liabilityShifted']) ? (bool) $payload['liabilityShifted'] : null; // Deprecated parameter
        $authenticationStatus = isset($payload['authenticationStatus']) ? $payload['authenticationStatus'] : null; // Deprecated parameter
        $authenticationReason = isset($payload['authenticationReason']) ? $payload['authenticationReason'] : null; // Deprecated parameter

        // Liability might shift to the card issuer.
        if ($liabilityShift === static::LIABILITY_SHIFT_POSSIBLE || $liabilityShift === static::LIABILITY_SHIFT_YES) {
            return static::PROCEED;
        }

        // Test cards
        if ($liabilityShift === null && $liabilityShifted === null && $authenticationStatus === 'APPROVED' && $authenticationReason === null) {
            return static::NO_DECISION;
        }

        // You have not required 3D Secure for the buyer or the card network did not require a 3D Secure.
        // You can continue with authorization and assume liability. If you prefer not to assume liability, ask the buyer for another card.
        if ($liabilityShift === null && $liabilityShifted === null && $authenticationStatus === null && $authenticationReason === null) {
            return static::NO_DECISION;
        }

        // Buyer successfully authenticated using 3D Secure.
        // Buyer authenticated with 3D Secure and you can continue with the authorization.
        if ($liabilityShifted && $authenticationStatus === 'YES' && $authenticationReason === 'SUCCESSFUL') {
            return static::PROCEED;
        }

        // An error occurred with the 3D Secure authentication system.
        // Prompt the buyer to re-authenticate or request for another form of payment.
        if (!$liabilityShifted && $authenticationStatus === 'ERROR' && $authenticationReason === 'ERROR') {
            return static::RETRY;
        }

        // Buyer was presented the 3D Secure challenge but chose to skip the authentication.
        // Do not continue with current authorization. Prompt the buyer to re-authenticate or request buyer for another form of payment.
        if (!$liabilityShifted && $authenticationStatus === 'NO' && $authenticationReason === 'SKIPPED_BY_BUYER') {
            return static::RETRY;
        }

        // Buyer may have failed the challenge or the device was not verified.
        // Do not continue with current authorization. Prompt the buyer to re-authenticate or request buyer for another form of payment.
        if (!$liabilityShifted && $authenticationStatus === 'NO' && $authenticationReason === 'FAILURE') {
            return static::RETRY;
        }

        // 3D Secure was skipped as authentication system did not require a challenge.
        // You can continue with the authorization and assume liability. If you prefer not to assume liability, ask the buyer for another card.
        if (!$liabilityShifted && $authenticationStatus === 'NO' && $authenticationReason === 'BYPASSED') {
            return static::NO_DECISION;
        }

        // Card is not enrolled in 3D Secure.
        // Card issuing bank is not participating in 3D Secure. Continue with authorization as authentication is not required.
        if (!$liabilityShifted && $authenticationStatus === 'NO' && $authenticationReason === 'ATTEMPTED') {
            return static::PROCEED;
        }

        // Issuing bank is not able to complete authentication.
        // You can continue with the authorization and assume liability. If you prefer not to assume liability, ask the buyer for another card.
        if (!$liabilityShifted && $authenticationStatus === 'NO' && $authenticationReason === 'UNAVAILABLE') {
            return static::NO_DECISION;
        }

        // Card is not eligible for 3D Secure authentication.
        // Continue with authorization as authentication is not required.
        if (!$liabilityShifted && $authenticationStatus === 'NO' && $authenticationReason === 'CARD_INELIGIBLE') {
            return static::PROCEED;
        }

        // Liability is with the merchant.
        if ($liabilityShift === static::LIABILITY_SHIFT_NO) {
            return static::REJECT;
        }

        // The authentication system is not available.
        if ($liabilityShift === static::LIABILITY_SHIFT_UNKNOWN) {
            return static::RETRY;
        }

        // Default case
        return static::NO_DECISION;
    }
}
