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

namespace Tests\Unit\PayPal;

use PHPUnit\Framework\TestCase;
use PrestaShop\Module\PrestashopCheckout\PayPal\Card3DSecureLegacy;

/**
 * Deprecated parameters related to 3D Secure 1.0
 * Recommended action based on `AuthenticationReason` and `authenticationStatus` parameters, a `liabilityShifted` determines how you might proceed with authentication
 *
 * @see https://developer.paypal.com/docs/checkout/advanced/customize/3d-secure/response-parameters/#link-deprecatedparameters
 */
class Card3DSecureLegacyTest extends TestCase
{
    /**
     * @dataProvider payloadProvider
     */
    public function testValidateContingencies(array $payload, $expectedResult)
    {
        $card3DSecureLegacy = new Card3DSecureLegacy();
        $actualResult = $card3DSecureLegacy->continueWithAuthorization($payload);
        $this->assertEquals($expectedResult, $actualResult);
    }

    public function payloadProvider()
    {
        return [
            // Liability might shift to the card issuer.
            [
                [
                    'liabilityShift' => 'POSSIBLE',
                    'liabilityShifted' => null,
                    'authenticationStatus' => null,
                    'authenticationReason' => null,
                ],
                Card3DSecureLegacy::PROCEED,
            ],
            // Liability is with the merchant.
            [
                [
                    'liabilityShift' => 'NO',
                    'liabilityShifted' => null,
                    'authenticationStatus' => null,
                    'authenticationReason' => null,
                ],
                Card3DSecureLegacy::REJECT,
            ],
            // The authentication system is not available.
            [
                [
                    'liabilityShift' => 'UNKNOWN',
                    'liabilityShifted' => null,
                    'authenticationStatus' => null,
                    'authenticationReason' => null,
                ],
                Card3DSecureLegacy::RETRY,
            ],
            // You have not required 3D Secure for the buyer or the card network did not require a 3D Secure.
            // You can continue with authorization and assume liability. If you prefer not to assume liability, ask the buyer for another card.
            [
                [
                    'liabilityShift' => null,
                    'liabilityShifted' => null,
                    'authenticationStatus' => null,
                    'authenticationReason' => null,
                ],
                Card3DSecureLegacy::NO_DECISION,
            ],
            // Buyer successfully authenticated using 3D Secure.
            // Buyer authenticated with 3D Secure and you can continue with the authorization.
            [
                [
                    'liabilityShift' => 'POSSIBLE',
                    'liabilityShifted' => true,
                    'authenticationStatus' => 'YES',
                    'authenticationReason' => 'SUCCESSFUL',
                ],
                Card3DSecureLegacy::PROCEED,
            ],
            // An error occurred with the 3D Secure authentication system.
            // Prompt the buyer to re-authenticate or request for another form of payment.
            [
                [
                    'liabilityShift' => 'NO',
                    'liabilityShifted' => false,
                    'authenticationStatus' => 'ERROR',
                    'authenticationReason' => 'ERROR',
                ],
                Card3DSecureLegacy::RETRY,
            ],
            // Buyer was presented the 3D Secure challenge but chose to skip the authentication.
            // Do not continue with current authorization. Prompt the buyer to re-authenticate or request buyer for another form of payment.
            [
                [
                    'liabilityShift' => null,
                    'liabilityShifted' => false,
                    'authenticationStatus' => 'NO',
                    'authenticationReason' => 'SKIPPED_BY_BUYER',
                ],
                Card3DSecureLegacy::RETRY,
            ],
            // Buyer may have failed the challenge or the device was not verified.
            // Do not continue with current authorization. Prompt the buyer to re-authenticate or request buyer for another form of payment.
            [
                [
                    'liabilityShift' => 'NO',
                    'liabilityShifted' => false,
                    'authenticationStatus' => 'NO',
                    'authenticationReason' => 'FAILURE',
                ],
                Card3DSecureLegacy::RETRY,
            ],
            // 3D Secure was skipped as authentication system did not require a challenge.
            // You can continue with the authorization and assume liability. If you prefer not to assume liability, ask the buyer for another card.
            [
                [
                    'liabilityShift' => null,
                    'liabilityShifted' => false,
                    'authenticationStatus' => 'NO',
                    'authenticationReason' => 'BYPASSED',
                ],
                Card3DSecureLegacy::NO_DECISION,
            ],
            // Card is not enrolled in 3D Secure.
            // Card issuing bank is not participating in 3D Secure. Continue with authorization as authentication is not required.
            [
                [
                    'liabilityShift' => null,
                    'liabilityShifted' => false,
                    'authenticationStatus' => 'NO',
                    'authenticationReason' => 'ATTEMPTED',
                ],
                Card3DSecureLegacy::PROCEED,
            ],
            // Cardholder is enrolled in 3DS however the Issuer is not supporting the program, resulting in a stand-in authentication experience
            [
                [
                    'liabilityShift' => 'POSSIBLE',
                    'liabilityShifted' => true,
                    'authenticationStatus' => 'ATTEMPTED',
                    'authenticationReason' => 'ATTEMPTED',
                ],
                Card3DSecureLegacy::PROCEED,
            ],
            // Issuing bank is not able to complete authentication.
            // You can continue with the authorization and assume liability. If you prefer not to assume liability, ask the buyer for another card.
            [
                [
                    'liabilityShift' => null,
                    'liabilityShifted' => false,
                    'authenticationStatus' => 'NO',
                    'authenticationReason' => 'UNAVAILABLE',
                ],
                Card3DSecureLegacy::NO_DECISION,
            ],
            // Card is not eligible for 3D Secure authentication.
            // Continue with authorization as authentication is not required.
            [
                [
                    'liabilityShift' => null,
                    'liabilityShifted' => false,
                    'authenticationStatus' => 'NO',
                    'authenticationReason' => 'CARD_INELIGIBLE',
                ],
                Card3DSecureLegacy::PROCEED,
            ],
        ];
    }
}
