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

namespace Tests\Unit\PayPal\Order\DTO;

use PHPUnit\Framework\TestCase;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\DTO\AmountWithBreakdown;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\DTO\CreatePayPalOrderRequest;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\DTO\PaymentSourceRequest;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\DTO\PayPalRequest;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\DTO\PayPalWalletExperienceContext;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\DTO\PurchaseUnitRequest;

class CreatePayPalOrderRequestTest extends TestCase
{
    /**
     * @var CreatePayPalOrderRequest
     */
    private $createPayPalOrderRequest;

    public function setUp(): void
    {
        $this->createPayPalOrderRequest = new CreatePayPalOrderRequest();
        $this->createPayPalOrderRequest->setIntent('CAPTURE');
        $this->createPayPalOrderRequest->setPurchaseUnits($this->createPurchaseUnits());
        $this->createPayPalOrderRequest->setPaymentSource($this->createPaymentSource());
    }

    public function testGetIntent()
    {
        $this->assertEquals('CAPTURE', $this->createPayPalOrderRequest->getIntent());
    }

    public function testGetPurchaseUnits()
    {
        $purchaseUnits = $this->createPayPalOrderRequest->getPurchaseUnits();
        $this->assertEquals(1, count($purchaseUnits));
        $this->assertInstanceOf(PurchaseUnitRequest::class, $purchaseUnits[0]);
    }

    public function testGetPaymentSource()
    {
        $this->assertInstanceOf(PaymentSourceRequest::class, $this->createPayPalOrderRequest->getPaymentSource());
    }

    /**
     * @return PurchaseUnitRequest[]
     */
    private function createPurchaseUnits()
    {
        $purchaseUnits = [];
        $amountWithBreakdown = new AmountWithBreakdown();
        $amountWithBreakdown->setValue('100.00');
        $amountWithBreakdown->setCurrencyCode('EUR');
        $purchaseUnitRequest = new PurchaseUnitRequest();
        $purchaseUnitRequest->setAmount($amountWithBreakdown);
        $purchaseUnits[] = $purchaseUnitRequest;

        return $purchaseUnits;
    }

    /**
     * @return PaymentSourceRequest
     */
    private function createPaymentSource()
    {
        $paymentSource = new PaymentSourceRequest();
        $paypal = new PayPalRequest();
        $experienceContext = new PayPalWalletExperienceContext();
        $experienceContext->setBrandName('PrestaShop');
        $experienceContext->setLandingPage('LOGIN');
        $experienceContext->setReturnUrl('https://www.prestashop.com/return');
        $experienceContext->setCancelUrl('https://www.prestashop.com/cancel');
        $paypal->setExperienceContext($experienceContext);
        $paymentSource->setPaypal($paypal);

        return $paymentSource;
    }
}
