<?php
/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0  Academic Free License (AFL 3.0)
 */

namespace PrestaShop\Module\PrestashopCheckout\PaymentOptions;

/**
 * Class PaymentOptions is used to create a full instance of payments options
 */
class PaymentOptions
{
    private $paymentOptions;

    /**
     * PaymentOptions constructor.
     *
     * @param PaymentOption[] $paymentOptions
     */
    public function __construct(array $paymentOptions = [])
    {
        $this->paymentOptions = $paymentOptions;
    }

    /**
     * @param PaymentOption $paymentOption
     */
    public function addPaymentOption(PaymentOption $paymentOption)
    {
        $this->paymentOptions[] = $paymentOption;
    }

    public function getPaymentOptionsAsArray($toDisplay = false)
    {
        $payments = [];
        foreach ($this->paymentOptions as $paymentOption) {
            $payments[] = (new PaymentOptionsPresenter())->convertToArray($paymentOption, $toDisplay);
        }

        return $payments;
    }

    public function getPaymentOptions()
    {
        return $this->paymentOptions;
    }
}
