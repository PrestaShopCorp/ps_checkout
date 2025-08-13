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

namespace PsCheckout\Core\PayPal\ApplePay\ValueObject;

/**
 * This class represents the "Total" data for an Apple Pay payment.
 *
 * @see https://developer.paypal.com/docs/checkout/apm/apple-pay/ Apple Pay Documentation
 */
class ApplePayTotalData
{
    const TYPE_FINAL = 'final';

    /**
     * @var string
     */
    private $type = self::TYPE_FINAL;

    /**
     * @var string
     */
    private $label;

    /**
     * @var string
     */
    private $amount;

    /**
     * @param string $label
     * @param string $amount
     */
    public function __construct(string $label, string $amount)
    {
        $this->label = $label;
        $this->amount = $amount;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @return string
     */
    public function getLabel(): string
    {
        return $this->label;
    }

    /**
     * @return string
     */
    public function getAmount(): string
    {
        return $this->amount;
    }
}
