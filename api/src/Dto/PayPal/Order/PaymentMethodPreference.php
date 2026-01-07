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

namespace PsCheckout\Api\Dto\PayPal\Order;

/**
 * The customer and merchant payment preferences.
 */
class PaymentMethodPreference
{
    /**
     * @var string|null
     */
    private $payeePreferred = PayeePaymentMethodPreference::UNRESTRICTED;

    /**
     * @var string|null
     */
    private $standardEntryClassCode = StandardEntryClassCode::WEB;

    /**
     * Returns Payee Preferred.
     * The merchant-preferred payment methods.
     */
    public function getPayeePreferred(): ?string
    {
        return $this->payeePreferred;
    }

    /**
     * Sets Payee Preferred.
     * The merchant-preferred payment methods.
     *
     * @maps payee_preferred
     */
    public function setPayeePreferred(?string $payeePreferred): void
    {
        $this->payeePreferred = $payeePreferred;
    }

    /**
     * Returns Standard Entry Class Code.
     * NACHA (the regulatory body governing the ACH network) requires that API callers (merchants,
     * partners) obtain the consumer’s explicit authorization before initiating a transaction. To stay
     * compliant, you’ll need to make sure that you retain a compliant authorization for each transaction
     * that you originate to the ACH Network using this API. ACH transactions are categorized (using SEC
     * codes) by how you capture authorization from the Receiver (the person whose bank account is being
     * debited or credited). PayPal supports the following SEC codes.
     */
    public function getStandardEntryClassCode(): ?string
    {
        return $this->standardEntryClassCode;
    }

    /**
     * Sets Standard Entry Class Code.
     * NACHA (the regulatory body governing the ACH network) requires that API callers (merchants,
     * partners) obtain the consumer’s explicit authorization before initiating a transaction. To stay
     * compliant, you’ll need to make sure that you retain a compliant authorization for each transaction
     * that you originate to the ACH Network using this API. ACH transactions are categorized (using SEC
     * codes) by how you capture authorization from the Receiver (the person whose bank account is being
     * debited or credited). PayPal supports the following SEC codes.
     *
     * @maps standard_entry_class_code
     */
    public function setStandardEntryClassCode(?string $standardEntryClassCode): void
    {
        $this->standardEntryClassCode = $standardEntryClassCode;
    }
}
