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

namespace PrestaShop\Module\PrestashopCheckout\Order\Command;

use DateTimeImmutable;
use PrestaShop\Module\PrestashopCheckout\Exception\NegativePaymentAmountException;
use PrestaShop\Module\PrestashopCheckout\Order\CheckoutAmount;
use PrestaShop\Module\PrestashopCheckout\Order\CheckoutOrderId;
use PrestaShop\Module\PrestashopCheckout\Exception\OrderConstraintException;

class AddOrderPaymentCommand
{
    /**
     * @var string
     */
    const INVALID_CHARACTERS_NAME = '<>={}';

    /**
     * @var string
     */
    const PATTERN_PAYMENT_METHOD_NAME = '/^[^' . self::INVALID_CHARACTERS_NAME . ']*$/u';

    /**
     * @var CheckoutOrderId
     */
    private $orderId;

    /**
     * @var DateTimeImmutable
     */
    private $paymentDate;

    /**
     * @var string
     */
    private $paymentMethod;

    /**
     * @var CheckoutAmount
     */
    private $paymentAmount;

    /**
     * @var int
     */
    private $paymentCurrencyId;

    /**
     * @var string
     */
    private $transactionId;

    /**
     * @param int $orderId
     * @param string $paymentDate
     * @param string $paymentMethod
     * @param string $paymentAmount
     * @param int $paymentCurrencyId
     * @param string $transactionId
     */
    public function __construct(
        $orderId,
        $paymentDate,
        $paymentMethod,
        $paymentAmount,
        $paymentCurrencyId,
        $transactionId = null
    )
    {
        $amount = new CheckoutAmount($paymentAmount);
        $this->assertAmountIsPositive($amount);
        $this->assertPaymentMethodIsGenericName($paymentMethod);


        $this->orderId = new CheckoutOrderId($orderId);

        $this->paymentDate = new DateTimeImmutable($paymentDate);
        $this->paymentMethod = $paymentMethod;
        $this->paymentAmount = $amount;
        $this->paymentCurrencyId = $paymentCurrencyId;
        $this->transactionId = $transactionId;
    }

    /**
     * @return CheckoutOrderId
     */
    public function getOrderId()
    {
        return $this->orderId;
    }

    /**
     * @return DateTimeImmutable
     */
    public function getPaymentDate()
    {
        return $this->paymentDate;
    }

    /**
     * @return string
     */
    public function getPaymentMethod()
    {
        return $this->paymentMethod;
    }

    /**
     * @return string
     */
    public function getPaymentAmount()
    {
        return $this->paymentAmount;
    }

    /**
     * @return int
     */
    public function getPaymentCurrencyId()
    {
        return $this->paymentCurrencyId;
    }

    /**
     * @return string
     */
    public function getPaymentTransactionId()
    {
        return $this->transactionId;
    }

    /**
     * @param CheckoutAmount $amount
     *
     * @return void
     *
     * @throws NegativePaymentAmountException
     */
    private function assertAmountIsPositive(CheckoutAmount $amount)
    {
        if ($amount->isNegative()) {
            throw new NegativePaymentAmountException('The amount should be greater than 0.');
        }
    }

    /**
     * @param string $paymentMethod
     */
    private function assertPaymentMethodIsGenericName($paymentMethod)
    {
        if (empty($paymentMethod) || !preg_match(self::PATTERN_PAYMENT_METHOD_NAME, $paymentMethod)) {
            throw new OrderConstraintException(
                'The selected payment method is invalid.',
                OrderConstraintException::INVALID_PAYMENT_METHOD
            );
        }
    }
}
