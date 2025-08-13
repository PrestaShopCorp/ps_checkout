<?php

namespace PsCheckout\Tests\Unit\PayPal\OrderStatus\Action;

use PHPUnit\Framework\TestCase;
use PsCheckout\Core\Order\Exception\OrderException;
use PsCheckout\Core\PayPal\Order\Configuration\PayPalOrderStatus;
use PsCheckout\Core\PayPal\OrderStatus\Action\PayPalCheckOrderStatusAction;

class PayPalCheckOrderStatusActionTest extends TestCase
{
    /** @var PayPalCheckOrderStatusAction */
    private $action;

    protected function setUp(): void
    {
        $this->action = new PayPalCheckOrderStatusAction();
    }

    /**
     * @dataProvider provideValidTransitions
     */
    public function testItValidatesAllowedTransitions(string $oldStatus, string $newStatus): void
    {
        $result = $this->action->execute($oldStatus, $newStatus);

        $this->assertTrue($result);
    }

    public function provideValidTransitions(): array
    {
        return [
            'created_to_approved' => [
                'oldStatus' => PayPalOrderStatus::CREATED,
                'newStatus' => PayPalOrderStatus::APPROVED,
            ],
            'created_to_pending_approval' => [
                'oldStatus' => PayPalOrderStatus::CREATED,
                'newStatus' => PayPalOrderStatus::PENDING_APPROVAL,
            ],
            'created_to_saved' => [
                'oldStatus' => PayPalOrderStatus::CREATED,
                'newStatus' => PayPalOrderStatus::SAVED,
            ],
            'created_to_payer_action_required' => [
                'oldStatus' => PayPalOrderStatus::CREATED,
                'newStatus' => PayPalOrderStatus::PAYER_ACTION_REQUIRED,
            ],
            'approved_to_completed' => [
                'oldStatus' => PayPalOrderStatus::APPROVED,
                'newStatus' => PayPalOrderStatus::COMPLETED,
            ],
            'approved_to_reversed' => [
                'oldStatus' => PayPalOrderStatus::APPROVED,
                'newStatus' => PayPalOrderStatus::REVERSED,
            ],
            'payer_action_required_to_approved' => [
                'oldStatus' => PayPalOrderStatus::PAYER_ACTION_REQUIRED,
                'newStatus' => PayPalOrderStatus::APPROVED,
            ],
        ];
    }

    /**
     * @dataProvider provideInvalidTransitions
     */
    public function testItRejectsDisallowedTransitions(string $oldStatus, string $newStatus): void
    {
        $result = $this->action->execute($oldStatus, $newStatus);

        $this->assertFalse($result);
    }

    public function provideInvalidTransitions(): array
    {
        return [
            'completed_to_approved' => [
                'oldStatus' => PayPalOrderStatus::COMPLETED,
                'newStatus' => PayPalOrderStatus::APPROVED,
            ],
            'voided_to_completed' => [
                'oldStatus' => PayPalOrderStatus::VOIDED,
                'newStatus' => PayPalOrderStatus::COMPLETED,
            ],
            'canceled_to_approved' => [
                'oldStatus' => PayPalOrderStatus::CANCELED,
                'newStatus' => PayPalOrderStatus::APPROVED,
            ],
            'reversed_to_completed' => [
                'oldStatus' => PayPalOrderStatus::REVERSED,
                'newStatus' => PayPalOrderStatus::COMPLETED,
            ],
            'saved_to_approved' => [
                'oldStatus' => PayPalOrderStatus::SAVED,
                'newStatus' => PayPalOrderStatus::APPROVED,
            ],
        ];
    }

    public function testItThrowsExceptionWhenOldStatusDoesNotExist(): void
    {
        $this->expectException(OrderException::class);
        $this->expectExceptionMessage('The oldStatus doesn\'t exist (123)');
        $this->expectExceptionCode(OrderException::STATUS_CHECK_AVAILABLE_BAD_PARAMETER);

        $this->action->execute(123, PayPalOrderStatus::APPROVED);
    }

    public function testItThrowsExceptionWhenOldStatusIsInvalid(): void
    {
        $this->expectException(OrderException::class);
        $this->expectExceptionMessage('The oldStatus doesn\'t exist (INVALID_STATUS)');
        $this->expectExceptionCode(OrderException::STATUS_CHECK_AVAILABLE_BAD_PARAMETER);

        $this->action->execute('INVALID_STATUS', PayPalOrderStatus::APPROVED);
    }
}
