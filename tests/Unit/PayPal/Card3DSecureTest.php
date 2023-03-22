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
use PrestaShop\Module\PrestashopCheckout\PayPal\Card3DSecure;

/**
 * Parameters related to 3D Secure 2.0
 * Recommended action based on `enrollment_status` and `authentication_status` parameters, a `liability_shift` determines how you might proceed with authentication
 *
 * @see https://developer.paypal.com/docs/checkout/advanced/customize/3d-secure/response-parameters/#enrollmentstatusauthentication_statusliabilityshiftrecommended-action
 */
class Card3DSecureTest extends TestCase
{
    /**
     * @dataProvider orderProvider
     */
    public function testCard3DSecure(array $order, $expectedResult)
    {
        $validator = new Card3DSecure();
        $actualResult = $validator->continueWithAuthorization($order);
        $this->assertEquals($expectedResult, $actualResult);
    }

    public function orderProvider()
    {
        return [
            [
                [
                    'payment_source' => [
                        'card' => [
                            'authentication_result' => [
                                'liability_shift' => Card3DSecure::LIABILITY_SHIFT_POSSIBLE,
                                'three_d_secure' => [
                                    'enrollment_status' => Card3DSecure::ENROLLMENT_STATUS_YES,
                                    'authentication_status' => Card3DSecure::AUTHENTICATION_RESULT_YES,
                                ],
                            ],
                        ],
                    ],
                ],
                Card3DSecure::PROCEED,
            ],
            [
                [
                    'payment_source' => [
                        'card' => [
                            'authentication_result' => [
                                'liability_shift' => Card3DSecure::LIABILITY_SHIFT_NO,
                                'three_d_secure' => [
                                    'enrollment_status' => Card3DSecure::ENROLLMENT_STATUS_YES,
                                    'authentication_status' => Card3DSecure::AUTHENTICATION_RESULT_NO,
                                ],
                            ],
                        ],
                    ],
                ],
                Card3DSecure::REJECT,
            ],
            [
                [
                    'payment_source' => [
                        'card' => [
                            'authentication_result' => [
                                'liability_shift' => Card3DSecure::LIABILITY_SHIFT_NO,
                                'three_d_secure' => [
                                    'enrollment_status' => Card3DSecure::ENROLLMENT_STATUS_YES,
                                    'authentication_status' => Card3DSecure::AUTHENTICATION_RESULT_REJECTED,
                                ],
                            ],
                        ],
                    ],
                ],
                Card3DSecure::REJECT,
            ],
            [
                [
                    'payment_source' => [
                        'card' => [
                            'authentication_result' => [
                                'liability_shift' => Card3DSecure::LIABILITY_SHIFT_POSSIBLE,
                                'three_d_secure' => [
                                    'enrollment_status' => Card3DSecure::ENROLLMENT_STATUS_YES,
                                    'authentication_status' => Card3DSecure::AUTHENTICATION_RESULT_ATTEMPTED,
                                ],
                            ],
                        ],
                    ],
                ],
                Card3DSecure::PROCEED,
            ],
            [
                [
                    'payment_source' => [
                        'card' => [
                            'authentication_result' => [
                                'liability_shift' => Card3DSecure::LIABILITY_SHIFT_UNKNOWN,
                                'three_d_secure' => [
                                    'enrollment_status' => Card3DSecure::ENROLLMENT_STATUS_YES,
                                    'authentication_status' => Card3DSecure::AUTHENTICATION_RESULT_UNABLE,
                                ],
                            ],
                        ],
                    ],
                ],
                Card3DSecure::RETRY,
            ],
            [
                [
                    'payment_source' => [
                        'card' => [
                            'authentication_result' => [
                                'liability_shift' => Card3DSecure::LIABILITY_SHIFT_NO,
                                'three_d_secure' => [
                                    'enrollment_status' => Card3DSecure::ENROLLMENT_STATUS_YES,
                                    'authentication_status' => Card3DSecure::AUTHENTICATION_RESULT_UNABLE,
                                ],
                            ],
                        ],
                    ],
                ],
                Card3DSecure::RETRY,
            ],
            [
                [
                    'payment_source' => [
                        'card' => [
                            'authentication_result' => [
                                'liability_shift' => Card3DSecure::LIABILITY_SHIFT_UNKNOWN,
                                'three_d_secure' => [
                                    'enrollment_status' => Card3DSecure::ENROLLMENT_STATUS_YES,
                                    'authentication_status' => Card3DSecure::AUTHENTICATION_RESULT_CHALLENGE_REQUIRED,
                                ],
                            ],
                        ],
                    ],
                ],
                Card3DSecure::RETRY,
            ],
            [
                [
                    'payment_source' => [
                        'card' => [
                            'authentication_result' => [
                                'liability_shift' => Card3DSecure::LIABILITY_SHIFT_NO,
                                'three_d_secure' => [
                                    'enrollment_status' => Card3DSecure::ENROLLMENT_STATUS_YES,
                                ],
                            ],
                        ],
                    ],
                ],
                Card3DSecure::RETRY,
            ],
            [
                [
                    'payment_source' => [
                        'card' => [
                            'authentication_result' => [
                                'liability_shift' => Card3DSecure::LIABILITY_SHIFT_NO,
                                'three_d_secure' => [
                                    'enrollment_status' => Card3DSecure::ENROLLMENT_STATUS_NO,
                                ],
                            ],
                        ],
                    ],
                ],
                Card3DSecure::PROCEED,
            ],
            [
                [
                    'payment_source' => [
                        'card' => [
                            'authentication_result' => [
                                'liability_shift' => Card3DSecure::LIABILITY_SHIFT_NO,
                                'three_d_secure' => [
                                    'enrollment_status' => Card3DSecure::ENROLLMENT_STATUS_UNAVAILABLE,
                                ],
                            ],
                        ],
                    ],
                ],
                Card3DSecure::PROCEED,
            ],
            [
                [
                    'payment_source' => [
                        'card' => [
                            'authentication_result' => [
                                'liability_shift' => Card3DSecure::LIABILITY_SHIFT_UNKNOWN,
                                'three_d_secure' => [
                                    'enrollment_status' => Card3DSecure::ENROLLMENT_STATUS_UNAVAILABLE,
                                ],
                            ],
                        ],
                    ],
                ],
                Card3DSecure::RETRY,
            ],
            [
                [
                    'payment_source' => [
                        'card' => [
                            'authentication_result' => [
                                'liability_shift' => Card3DSecure::LIABILITY_SHIFT_NO,
                                'three_d_secure' => [
                                    'enrollment_status' => Card3DSecure::ENROLLMENT_STATUS_BYPASS,
                                ],
                            ],
                        ],
                    ],
                ],
                Card3DSecure::PROCEED,
            ],
            [
                [
                    'payment_source' => [
                        'card' => [
                            'authentication_result' => [
                                'liability_shift' => Card3DSecure::LIABILITY_SHIFT_UNKNOWN,
                            ],
                        ],
                    ],
                ],
                Card3DSecure::RETRY,
            ],
            [
                [
                    'payment_source' => [
                        'card' => [
                            'last_digits' => '1083',
                            'brand' => 'VISA',
                            'type' => 'UNKNOWN',
                        ],
                    ],
                ],
                Card3DSecure::NO_DECISION,
            ],
        ];
    }
}
