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

namespace PsCheckout\Core\Order\ValueObject;

/**
 * Class ValidateOrderData
 */
class ValidateOrderData
{
    /**
     * @var int
     */
    private $cartId;

    /**
     * @var int
     */
    private $orderStateId;

    /**
     * @var float
     */
    private $paidAmount;

    /**
     * @var array
     */
    private $extraVars;

    /**
     * @var int
     */
    private $currencyId;

    /**
     * @var string
     */
    private $secureKey;

    /**
     * @var string
     */
    private $fundingSource;

    /**
     * ValidateOrderData constructor.
     *
     * @param int $cartId
     * @param int $orderStateId
     * @param float $paidAmount
     * @param array $extraVars
     * @param int $currencyId
     * @param string $secureKey
     * @param string $fundingSource
     */
    public function __construct(
        int $cartId,
        int $orderStateId,
        float $paidAmount,
        array $extraVars,
        int $currencyId,
        string $secureKey,
        string $fundingSource
    ) {
        $this->cartId = $cartId;
        $this->orderStateId = $orderStateId;
        $this->paidAmount = $paidAmount;
        $this->extraVars = $extraVars;
        $this->currencyId = $currencyId;
        $this->secureKey = $secureKey;
        $this->fundingSource = $fundingSource;
    }

    /**
     * @return int
     */
    public function getCartId(): int
    {
        return $this->cartId;
    }

    /**
     * @return int
     */
    public function getOrderStateId(): int
    {
        return $this->orderStateId;
    }

    /**
     * @return float
     */
    public function getPaidAmount(): float
    {
        return $this->paidAmount;
    }

    /**
     * @return array
     */
    public function getExtraVars(): array
    {
        return $this->extraVars;
    }

    /**
     * @return int
     */
    public function getCurrencyId(): int
    {
        return $this->currencyId;
    }

    /**
     * @return string
     */
    public function getSecureKey(): string
    {
        return $this->secureKey;
    }

    /**
     * @return string
     */
    public function getFundingSource(): string
    {
        return $this->fundingSource;
    }

    /**
     * Creates a new instance of ValidateOrderData.
     *
     * @param int $cartId
     * @param int $orderStateId
     * @param float $paidAmount
     * @param array $extraVars
     * @param int $currencyId
     * @param string $secureKey
     * @param string $fundingSource
     *
     * @return ValidateOrderData
     */
    public static function create(
        int $cartId,
        int $orderStateId,
        float $paidAmount,
        array $extraVars,
        int $currencyId,
        string $secureKey,
        string $fundingSource
    ): ValidateOrderData {
        return new self($cartId, $orderStateId, $paidAmount, $extraVars, $currencyId, $secureKey, $fundingSource);
    }
}
