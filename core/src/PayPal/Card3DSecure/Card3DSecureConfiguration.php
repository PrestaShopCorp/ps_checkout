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

class Card3DSecureConfiguration
{
    // Liability Shift Constants
    const LIABILITY_SHIFT_YES = 'YES';          // Liability has shifted to the card issuer

    const LIABILITY_SHIFT_NO = 'NO';            // Liability is with the merchant

    const LIABILITY_SHIFT_POSSIBLE = 'POSSIBLE'; // Liability may shift to the card issuer

    const LIABILITY_SHIFT_UNKNOWN = 'UNKNOWN';  // The authentication system is not available

    // Enrollment Status Constants
    const ENROLLMENT_STATUS_YES = 'Y';          // Bank is participating in 3DS protocol

    const ENROLLMENT_STATUS_NO = 'N';           // Bank is not participating

    const ENROLLMENT_STATUS_UNAVAILABLE = 'U';  // DS/ACS not available at time of request

    const ENROLLMENT_STATUS_BYPASS = 'B';       // Merchant authentication rule triggered to bypass

    // Authentication Result Constants
    const AUTH_RESULT_YES = 'Y';                // Successful authentication

    const AUTH_RESULT_NO = 'N';                 // Failed authentication / transaction denied

    const AUTH_RESULT_UNABLE = 'U';             // Unable to complete authentication

    const AUTH_RESULT_REJECTED = 'R';           // Authentication rejected

    const AUTH_RESULT_ATTEMPTED = 'A';          // Authentication attempted

    const AUTH_RESULT_CHALLENGE_REQUIRED = 'C'; // Challenge required for authentication

    const AUTH_RESULT_DECOUPLED = 'D';          // Decoupled authentication confirmed

    const AUTH_RESULT_INFO = 'I';               // Informational, 3DS requester challenge preference acknowledged

    // Decision Constants
    const DECISION_NO_DECISION = 0;             // No decision made

    const DECISION_PROCEED = 1;                 // Continue with authorization

    const DECISION_REJECT = 2;                  // Do not continue with authorization

    const DECISION_RETRY = 3;                   // Do not continue, ask cardholder to retry
}
