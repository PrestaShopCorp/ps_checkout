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

namespace PsCheckout\Core\Order\Builder;

use PsCheckout\Core\Exception\PsCheckoutException;

interface OrderPayloadBuilderInterface
{
    /**
     * Builds the order payload.
     *
     * @param bool $isFullPayload whether to build a full payload (true) or minimal payload (false)
     *
     * @return array the constructed payload
     *
     * @throws PsCheckoutException if required fields are missing
     */
    public function build(bool $isFullPayload = true): array;

    /**
     * @param array $cart
     */
    public function setCart(array $cart);

    /**
     * @param bool $isUpdate
     */
    public function setIsUpdate(bool $isUpdate);

    /**
     * @param bool $isExpressCheckout
     *
     * @return OrderPayloadBuilder
     */
    public function setIsExpressCheckout(bool $isExpressCheckout): OrderPayloadBuilder;

    /**
     * @param bool $savePaymentMethod
     */
    public function setSavePaymentMethod(bool $savePaymentMethod): OrderPayloadBuilder;

    /**
     * @param string $fundingSource
     */
    public function setFundingSource(string $fundingSource): OrderPayloadBuilder;

    /**
     * @param string $paypalCustomerId
     */
    public function setPaypalCustomerId(string $paypalCustomerId): OrderPayloadBuilder;

    /**
     * @param string $paypalVaultId
     */
    public function setPaypalVaultId(string $paypalVaultId): OrderPayloadBuilder;

    /**
     * @param string $paypalOrderId
     */
    public function setPaypalOrderId(string $paypalOrderId): OrderPayloadBuilder;

    /**
     * @param bool $isCard
     */
    public function setIsCard(bool $isCard): OrderPayloadBuilder;

    /**
     * @param bool $isVault
     */
    public function setIsVault(bool $isVault): OrderPayloadBuilder;
}
