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

namespace PsCheckout\Api\Dto\PayPal;

/**
 * NACHA (the regulatory body governing the ACH network) requires that API callers (merchants,
 * partners) obtain the consumer’s explicit authorization before initiating a transaction. To stay
 * compliant, you’ll need to make sure that you retain a compliant authorization for each transaction
 * that you originate to the ACH Network using this API. ACH transactions are categorized (using SEC
 * codes) by how you capture authorization from the Receiver (the person whose bank account is being
 * debited or credited). PayPal supports the following SEC codes.
 */
class StandardEntryClassCode
{
    /**
     * The API caller (merchant/partner) accepts authorization and payment information from a consumer over
     * the telephone.
     */
    public const TEL = 'TEL';

    /**
     * The API caller (merchant/partner) accepts Debit transactions from a consumer on their website.
     */
    public const WEB = 'WEB';

    /**
     * Cash concentration and disbursement for corporate debit transaction. Used to disburse or consolidate
     * funds. Entries are usually Optional high-dollar, low-volume, and time-critical. (e.g. intra-company
     * transfers or invoice payments to suppliers).
     */
    public const CCD = 'CCD';

    /**
     * Prearranged payment and deposit entries. Used for debit payments authorized by a consumer account
     * holder, and usually initiated by a company. These are usually recurring debits (such as insurance
     * premiums).
     */
    public const PPD = 'PPD';
}
