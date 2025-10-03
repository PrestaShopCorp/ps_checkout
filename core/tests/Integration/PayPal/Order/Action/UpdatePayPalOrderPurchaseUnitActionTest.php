<?php

namespace PsCheckout\Core\Tests\Integration\PayPal\Order\Action;

use PsCheckout\Api\ValueObject\PayPalOrderResponse;
use PsCheckout\Core\PayPal\Order\Action\UpdatePayPalOrderPurchaseUnitAction;
use PsCheckout\Core\Tests\Integration\BaseTestCase;
use PsCheckout\Infrastructure\Repository\PayPalOrderPurchaseUnitRepository;

class UpdatePayPalOrderPurchaseUnitActionTest extends BaseTestCase
{
    /** @var \Db */
    private $db;

    private ?UpdatePayPalOrderPurchaseUnitAction $updatePayPalOrderPurchaseUnitAction;

    protected function setUp(): void
    {
        parent::setUp();
        $this->db = \Db::getInstance();
        $this->updatePayPalOrderPurchaseUnitAction = $this->getService(UpdatePayPalOrderPurchaseUnitAction::class);
    }

    public function testItUpdatesPurchaseUnitWithCapture(): void
    {
        $orderResponse = new PayPalOrderResponse(
            'PAY-123',
            'COMPLETED',
            'CAPTURE',
            [], // payer
            [], // payment_source
            [
                [
                    'reference_id' => 'default',
                    'items' => [
                        [
                            'name' => 'Test Product',
                            'quantity' => '1',
                            'unit_amount' => [
                                'currency_code' => 'EUR',
                                'value' => '10.00',
                            ],
                        ],
                    ],
                    'payments' => [
                        'captures' => [
                            [
                                'id' => 'CAP-123',
                                'status' => 'COMPLETED',
                                'create_time' => '2024-01-01T10:00:00Z',
                                'update_time' => '2024-01-01T10:01:00Z',
                                'seller_protection' => ['status' => 'ELIGIBLE'],
                                'seller_receivable_breakdown' => ['gross_amount' => ['value' => '10.00']],
                                'final_capture' => true,
                            ],
                        ],
                    ],
                ],
            ],
            [], // links
            '2024-01-01T10:00:00Z' // create_time
        );

        $this->updatePayPalOrderPurchaseUnitAction->execute($orderResponse);

        // Verify purchase unit was saved
        $query = new \DbQuery();
        $query->select('*')
            ->from(PayPalOrderPurchaseUnitRepository::TABLE_NAME)
            ->where('id_order = "' . pSQL('PAY-123') . '"');

        $result = $this->db->getRow($query);

        $this->assertNotEmpty($result);
        $this->assertEquals('default', $result['reference_id']);
        $this->assertNotEmpty($result['items']);

        // Verify capture was saved
        $query = new \DbQuery();
        $query->select('*')
            ->from('pscheckout_capture')
            ->where('id = "' . pSQL('CAP-123') . '"');

        $capture = $this->db->getRow($query);
        $this->assertNotEmpty($capture);
        $this->assertEquals('COMPLETED', $capture['status']);
    }

    public function testItUpdatesPurchaseUnitWithAuthorization(): void
    {
        $orderResponse = new PayPalOrderResponse(
            'PAY-123',
            'COMPLETED',
            'AUTHORIZE',
            [], // payer
            [], // payment_source
            [
                [
                    'reference_id' => 'default',
                    'items' => [
                        [
                            'name' => 'Test Product',
                            'quantity' => '1',
                            'unit_amount' => [
                                'currency_code' => 'EUR',
                                'value' => '10.00',
                            ],
                        ],
                    ],
                    'payments' => [
                        'authorizations' => [
                            [
                                'id' => 'AUTH-123',
                                'status' => 'CREATED',
                                'expiration_time' => '2024-02-01T10:00:00Z',
                                'seller_protection' => ['status' => 'ELIGIBLE'],
                            ],
                        ],
                    ],
                ],
            ],
            [], // links
            '2024-01-01T10:00:00Z' // create_time
        );

        $this->updatePayPalOrderPurchaseUnitAction->execute($orderResponse);

        // Verify purchase unit was saved
        $query = new \DbQuery();
        $query->select('*')
            ->from(PayPalOrderPurchaseUnitRepository::TABLE_NAME)
            ->where('id_order = "' . pSQL('PAY-123') . '"');

        $result = $this->db->getRow($query);

        $this->assertNotEmpty($result);
        $this->assertEquals('default', $result['reference_id']);

        // Verify authorization was saved
        $query = new \DbQuery();
        $query->select('*')
            ->from('pscheckout_authorization')
            ->where('id = "' . pSQL('AUTH-123') . '"');

        $authorization = $this->db->getRow($query);
        $this->assertNotEmpty($authorization);
        $this->assertEquals('CREATED', $authorization['status']);
    }
}
