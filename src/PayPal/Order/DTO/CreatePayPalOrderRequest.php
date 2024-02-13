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

namespace PrestaShop\Module\PrestashopCheckout\PayPal\Order\DTO;

class CreatePayPalOrderRequest
{
    /**
     * @var string
     */
    private $intent;
    /**
     * @var PurchaseUnitRequest[]
     */
    private $purchase_units;
    /**
     * @var PaymentSourceRequest
     */
    private $payment_source;
    /**
     * @var ApplicationContextRequest
     */
    private $application_context;
    /**
     * @var string
     */
    private $processing_instruction;

    /**
     * @return string
     */
    public function getIntent()
    {
        return $this->intent;
    }

    /**
     * @param string $intent
     *
     * @return void
     */
    public function setIntent($intent)
    {
        $this->intent = $intent;
    }

    /**
     * @return PurchaseUnitRequest[]
     */
    public function getPurchaseUnits()
    {
        return $this->purchase_units;
    }

    /**
     * @param PurchaseUnitRequest[] $purchase_units
     *
     * @return void
     */
    public function setPurchaseUnits(array $purchase_units)
    {
        $this->purchase_units = $purchase_units;
    }

    /**
     * @return PaymentSourceRequest
     */
    public function getPaymentSource()
    {
        return $this->payment_source;
    }

    /**
     * @param PaymentSourceRequest $payment_source
     *
     * @return void
     */
    public function setPaymentSource(PaymentSourceRequest $payment_source)
    {
        $this->payment_source = $payment_source;
    }

    /**
     * @return ApplicationContextRequest
     */
    public function getApplicationContext()
    {
        return $this->application_context;
    }

    /**
     * @param ApplicationContextRequest $application_context
     *
     * @return void
     */
    public function setApplicationContext(ApplicationContextRequest $application_context)
    {
        $this->application_context = $application_context;
    }

    /**
     * @return string
     */
    public function getProcessingInstruction()
    {
        return $this->processing_instruction;
    }

    /**
     * @param string $processing_instruction
     *
     * @return void
     */
    public function setProcessingInstruction($processing_instruction)
    {
        $this->processing_instruction = $processing_instruction;
    }
}
