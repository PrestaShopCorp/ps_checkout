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

namespace PsCheckout\Core\Tests\Integration\Order\Validator;

use PsCheckout\Core\Order\Validator\CartOwnershipValidator;
use PsCheckout\Core\PayPal\Order\Action\CancelPayPalOrderAction;
use PsCheckout\Core\PayPal\Order\Configuration\PayPalOrderStatus;
use PsCheckout\Core\PayPal\Order\Entity\PayPalOrder;
use PsCheckout\Core\PayPal\Order\Request\ValueObject\CancelPayPalOrderRequest;
use PsCheckout\Core\Tests\Integration\BaseTestCase;
use PsCheckout\Core\Tests\Integration\Factory\CartFactory;
use PsCheckout\Core\Tests\Integration\Factory\PayPalOrderFactory;
use PsCheckout\Infrastructure\Repository\PayPalOrderRepository;

/**
 * Regression of XO-3203: an anonymous visitor who starts an express checkout from the product
 * or cart page owns a PayPal Order linked to a cart without customer. The check and cancel
 * controllers must accept that visitor's own order and refuse any other one.
 */
class CartOwnershipValidatorTest extends BaseTestCase
{
    /** @var CartOwnershipValidator */
    private $validator;

    /** @var PayPalOrderRepository */
    private $payPalOrderRepository;

    /** @var \Context */
    private $context;

    protected function setUp(): void
    {
        parent::setUp();

        $this->validator = $this->getService(CartOwnershipValidator::class);
        $this->payPalOrderRepository = $this->getService(PayPalOrderRepository::class);

        $context = \Context::getContext();
        $this->assertNotNull($context);
        $this->context = $context;
    }

    protected function tearDown(): void
    {
        // NOTE: no other integration test sets or reads the context cart and customer
        $this->context->cart = new \Cart();
        $this->context->customer = new \Customer();

        parent::tearDown();
    }

    public function testAnonymousVisitorOwnsThePayPalOrderOfTheirContextCart(): void
    {
        $cart = $this->setAnonymousVisitorWithCart();

        $this->assertFalse($this->validator->isOwnedByCurrentCustomer((int) $cart->id), 'validate and payment keep refusing a visitor without customer');
        $this->assertTrue($this->validator->isOwnedByCurrentVisitor((int) $cart->id));
    }

    public function testAnonymousVisitorCanCancelThePayPalOrderOfTheirContextCart(): void
    {
        $cart = $this->setAnonymousVisitorWithCart();
        $this->payPalOrderRepository->savePayPalOrder(PayPalOrderFactory::create([
            'id' => 'EXPRESS-OWN',
            'id_cart' => (int) $cart->id,
            'status' => PayPalOrderStatus::PAYER_ACTION_REQUIRED,
            'is_express_checkout' => true,
        ]));

        /** @var PayPalOrder $payPalOrder */
        $payPalOrder = $this->payPalOrderRepository->getOneBy(['id' => 'EXPRESS-OWN']);
        $this->assertTrue($this->validator->isOwnedByCurrentVisitor($payPalOrder->getIdCart()));

        /** @var CancelPayPalOrderAction $cancelPayPalOrderAction */
        $cancelPayPalOrderAction = $this->getService(CancelPayPalOrderAction::class);
        $cancelPayPalOrderAction->execute(new CancelPayPalOrderRequest([
            'orderID' => 'EXPRESS-OWN',
            'fundingSource' => 'paypal',
            'isExpressCheckout' => true,
            'reason' => 'checkout_cancelled',
        ], (int) $cart->id));

        /** @var PayPalOrder $cancelledOrder */
        $cancelledOrder = $this->payPalOrderRepository->getOneBy(['id' => 'EXPRESS-OWN']);
        $this->assertSame(PayPalOrderStatus::CANCELED, $cancelledOrder->getStatus());
        $this->assertSame((int) $cart->id, $cancelledOrder->getIdCart());
    }

    public function testAnonymousVisitorDoesNotOwnThePayPalOrderOfAnotherCart(): void
    {
        $this->setAnonymousVisitorWithCart();
        $otherAnonymousCart = CartFactory::create(['id_customer' => 0]);
        $customerCart = CartFactory::create(['id_customer' => 1]);

        $this->assertFalse($this->validator->isOwnedByCurrentVisitor((int) $otherAnonymousCart->id));
        $this->assertFalse($this->validator->isOwnedByCurrentVisitor((int) $customerCart->id));
    }

    public function testVisitorWithoutCartInContextOwnsNothing(): void
    {
        $this->context->cart = new \Cart();
        $this->context->customer = new \Customer();
        $cart = CartFactory::create(['id_customer' => 0]);

        $this->assertFalse($this->validator->isOwnedByCurrentVisitor((int) $cart->id));
    }

    /**
     * @return \Cart
     */
    private function setAnonymousVisitorWithCart(): \Cart
    {
        $cart = CartFactory::create(['id_customer' => 0]);

        $this->context->cart = $cart;
        $this->context->customer = new \Customer();

        return $cart;
    }
}
