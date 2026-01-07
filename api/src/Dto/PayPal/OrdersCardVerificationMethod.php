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
 * The method used for card verification.
 */
class OrdersCardVerificationMethod
{
    /**
     * Selecting this option will attempt to force a strong customer authentication for the
     * authorization/transaction. In countries where SCA has been defined and implemented it will result in
     * a contingency and HATEOAS link being returned.  The API caller should redirect the payer to that
     * link so that they can authenticate themselves against their issuing bank or other entity. As noted,
     * the HATEOAS link is only available in all regions where strong authentication is supported, (e.g. in
     * European countries where 3DS is live). Merchants can use this setting as an additional layer of
     * security if they choose to. In all cases, when an authorization is requested the AVS/CVV results
     * will be returned in the response.
     */
    public const SCA_ALWAYS = 'SCA_ALWAYS';

    /**
     * This is the default. When an authorization or transaction is attempted this option will return a
     * contingency and HATEOAS link only when local regulations require strong customer authentication, (e.
     * g. 3DS in countries and use cases where it is mandated). The API caller should redirect the payer to
     * the link so that they can authenticate themselves. In all cases, when an authorization is requested
     * the AVS/CVV results will be returned in the response.
     */
    public const SCA_WHEN_REQUIRED = 'SCA_WHEN_REQUIRED';

    /**
     * The contingency surfaced as an additional security layer that helps prevent unauthorized card-not-
     * present transactions and protects the merchant from exposure to fraud.
     */
    public const ENUM_3D_SECURE = '3D_SECURE';

    /**
     * Places a temporary hold on the card to ensure its validity. This process protects the merchant from
     * exposure to fraud. This verification method will confirm that the address information or CVV
     * included matches what the issuing bank has on file for the associated card, ensuring that only
     * authorized card users are able to make purchases from you.
     */
    public const AVS_CVV = 'AVS_CVV';
}
