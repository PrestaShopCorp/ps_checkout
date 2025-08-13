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

namespace PsCheckout\Core\PaymentToken\Repository;

use PsCheckout\Core\Exception\PsCheckoutException;
use PsCheckout\Core\PaymentToken\ValueObject\PaymentToken;

interface PaymentTokenRepositoryInterface
{
    /**
     * @param int|null $customerId
     * @param string|null $merchantId
     *
     * @return int
     *
     * @throws PsCheckoutException
     */
    public function getCount($customerId = null, $merchantId = null): int;

    /**
     * @param int $customerId
     * @param string $merchantId
     *
     * @return array
     *
     * @throws PsCheckoutException
     */
    public function findVaultedTokensByCustomerAndMerchant(int $customerId, string $merchantId): array;

    /**
     * @param string $getVaultId
     *
     * @return PaymentToken|null
     */
    public function getOneById(string $getVaultId);

    /**
     * @param PaymentToken $token
     *
     * @return void
     */
    public function save(PaymentToken $token);

    /**
     * @param string $tokenId
     * @param string $customerId
     *
     * @return void
     */
    public function setTokenFavorite(string $tokenId, string $customerId);

    /**
     * @param string $vaultId
     * @param int $customerId
     *
     * @return void
     */
    public function delete(string $vaultId);

    /**
     * @param int $customerId
     *
     * @return PaymentToken[]
     */
    public function getAllByCustomerId(int $customerId): array;
}
