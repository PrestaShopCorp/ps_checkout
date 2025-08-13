<?php

namespace PsCheckout\Tests\Unit\PayPal\Card3DSecure;

use PHPUnit\Framework\TestCase;
use PsCheckout\Api\ValueObject\PayPalOrderResponse;
use PsCheckout\Core\PayPal\Card3DSecure\Card3DSecureConfiguration;
use PsCheckout\Core\PayPal\Card3DSecure\Card3DSecureValidator;

class Card3DSecureValidatorTest extends TestCase
{
    /** @var Card3DSecureValidator */
    private $validator;

    protected function setUp(): void
    {
        $this->validator = new Card3DSecureValidator();
    }

    /**
     * @dataProvider provideAuthorizationDecisionScenarios
     */
    public function testGetAuthorizationDecision(
        ?array $authResult,
        ?string $liabilityShift,
        ?array $threeDSecure,
        int $expectedDecision
    ): void {
        $payPalOrder = $this->createMock(PayPalOrderResponse::class);
        $payPalOrder->method('getAuthenticationResult')->willReturn($authResult);
        $payPalOrder->method('getLiabilityShift')->willReturn($liabilityShift);
        $payPalOrder->method('get3dSecure')->willReturn($threeDSecure);

        $this->assertEquals($expectedDecision, $this->validator->getAuthorizationDecision($payPalOrder));
    }

    public function provideAuthorizationDecisionScenarios(): array
    {
        return [
            'no_auth_result' => [
                'authResult' => null,
                'liabilityShift' => null,
                'threeDSecure' => null,
                'expectedDecision' => Card3DSecureConfiguration::DECISION_NO_DECISION,
            ],
            'liability_shift_possible' => [
                'authResult' => ['status' => 'Y'],
                'liabilityShift' => Card3DSecureConfiguration::LIABILITY_SHIFT_POSSIBLE,
                'threeDSecure' => null,
                'expectedDecision' => Card3DSecureConfiguration::DECISION_PROCEED,
            ],
            'liability_shift_unknown' => [
                'authResult' => ['status' => 'Y'],
                'liabilityShift' => Card3DSecureConfiguration::LIABILITY_SHIFT_UNKNOWN,
                'threeDSecure' => null,
                'expectedDecision' => Card3DSecureConfiguration::DECISION_RETRY,
            ],
            'no_liability_shift_bypass_no_auth' => [
                'authResult' => ['status' => 'Y'],
                'liabilityShift' => Card3DSecureConfiguration::LIABILITY_SHIFT_NO,
                'threeDSecure' => [
                    'enrollment_status' => Card3DSecureConfiguration::ENROLLMENT_STATUS_BYPASS,
                ],
                'expectedDecision' => Card3DSecureConfiguration::DECISION_PROCEED,
            ],
            'no_liability_shift_unavailable_no_auth' => [
                'authResult' => ['status' => 'Y'],
                'liabilityShift' => Card3DSecureConfiguration::LIABILITY_SHIFT_NO,
                'threeDSecure' => [
                    'enrollment_status' => Card3DSecureConfiguration::ENROLLMENT_STATUS_UNAVAILABLE,
                ],
                'expectedDecision' => Card3DSecureConfiguration::DECISION_PROCEED,
            ],
            'no_liability_shift_rejected' => [
                'authResult' => ['status' => 'Y'],
                'liabilityShift' => Card3DSecureConfiguration::LIABILITY_SHIFT_NO,
                'threeDSecure' => [
                    'authentication_status' => Card3DSecureConfiguration::AUTH_RESULT_REJECTED,
                ],
                'expectedDecision' => Card3DSecureConfiguration::DECISION_REJECT,
            ],
            'no_liability_shift_auth_no' => [
                'authResult' => ['status' => 'Y'],
                'liabilityShift' => Card3DSecureConfiguration::LIABILITY_SHIFT_NO,
                'threeDSecure' => [
                    'authentication_status' => Card3DSecureConfiguration::AUTH_RESULT_NO,
                ],
                'expectedDecision' => Card3DSecureConfiguration::DECISION_REJECT,
            ],
            'no_liability_shift_auth_unable' => [
                'authResult' => ['status' => 'Y'],
                'liabilityShift' => Card3DSecureConfiguration::LIABILITY_SHIFT_NO,
                'threeDSecure' => [
                    'authentication_status' => Card3DSecureConfiguration::AUTH_RESULT_UNABLE,
                ],
                'expectedDecision' => Card3DSecureConfiguration::DECISION_RETRY,
            ],
            'no_liability_shift_no_auth_status' => [
                'authResult' => ['status' => 'Y'],
                'liabilityShift' => Card3DSecureConfiguration::LIABILITY_SHIFT_NO,
                'threeDSecure' => [
                    'enrollment_status' => 'OTHER',
                ],
                'expectedDecision' => Card3DSecureConfiguration::DECISION_RETRY,
            ],
            'default_case' => [
                'authResult' => ['status' => 'Y'],
                'liabilityShift' => 'OTHER',
                'threeDSecure' => null,
                'expectedDecision' => Card3DSecureConfiguration::DECISION_NO_DECISION,
            ],
        ];
    }

    /**
     * @dataProvider provide3DSecureAvailabilityScenarios
     */
    public function testIs3DSecureAvailable(string $enrollmentStatus, bool $expected): void
    {
        $payPalOrder = $this->createMock(PayPalOrderResponse::class);
        $payPalOrder->method('get3dSecureEnrollmentStatus')->willReturn($enrollmentStatus);

        $this->assertEquals($expected, $this->validator->is3DSecureAvailable($payPalOrder));
    }

    public function provide3DSecureAvailabilityScenarios(): array
    {
        return [
            'enrollment_yes' => [
                'enrollmentStatus' => Card3DSecureConfiguration::ENROLLMENT_STATUS_YES,
                'expected' => true,
            ],
            'enrollment_unavailable' => [
                'enrollmentStatus' => Card3DSecureConfiguration::ENROLLMENT_STATUS_UNAVAILABLE,
                'expected' => true,
            ],
            'enrollment_no' => [
                'enrollmentStatus' => Card3DSecureConfiguration::ENROLLMENT_STATUS_NO,
                'expected' => false,
            ],
            'enrollment_bypass' => [
                'enrollmentStatus' => Card3DSecureConfiguration::ENROLLMENT_STATUS_BYPASS,
                'expected' => false,
            ],
        ];
    }

    /**
     * @dataProvider provideLiabilityShiftScenarios
     */
    public function testIsLiabilityShifted(string $liabilityShift, string $authStatus, bool $expected): void
    {
        $payPalOrder = $this->createMock(PayPalOrderResponse::class);
        $payPalOrder->method('getLiabilityShift')->willReturn($liabilityShift);
        $payPalOrder->method('get3dSecureAuthenticationStatus')->willReturn($authStatus);

        $this->assertEquals($expected, $this->validator->isLiabilityShifted($payPalOrder));
    }

    public function provideLiabilityShiftScenarios(): array
    {
        return [
            'liability_yes_auth_yes' => [
                'liabilityShift' => Card3DSecureConfiguration::LIABILITY_SHIFT_YES,
                'authStatus' => Card3DSecureConfiguration::AUTH_RESULT_YES,
                'expected' => true,
            ],
            'liability_possible_auth_yes' => [
                'liabilityShift' => Card3DSecureConfiguration::LIABILITY_SHIFT_POSSIBLE,
                'authStatus' => Card3DSecureConfiguration::AUTH_RESULT_YES,
                'expected' => true,
            ],
            'liability_yes_auth_no' => [
                'liabilityShift' => Card3DSecureConfiguration::LIABILITY_SHIFT_YES,
                'authStatus' => Card3DSecureConfiguration::AUTH_RESULT_NO,
                'expected' => false,
            ],
            'liability_no_auth_yes' => [
                'liabilityShift' => Card3DSecureConfiguration::LIABILITY_SHIFT_NO,
                'authStatus' => Card3DSecureConfiguration::AUTH_RESULT_YES,
                'expected' => false,
            ],
        ];
    }
}
