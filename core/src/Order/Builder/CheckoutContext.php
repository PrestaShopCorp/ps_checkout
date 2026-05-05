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

class CheckoutContext implements CheckoutContextInterface
{
    /** @var array<string, mixed> */
    private $cart;

    /** @var string */
    private $fundingSource;

    /** @var bool */
    private $savePaymentMethod;

    /** @var string|null */
    private $paypalCustomerId;

    /** @var string|null */
    private $paypalVaultId;

    /** @var bool */
    private $expressCheckout;

    /** @var bool */
    private $isUpdate;

    /** @var string|null */
    private $birthDate;

    /** @var string|null */
    private $phone;

    public function __construct(
        array $cart,
        string $fundingSource,
        bool $savePaymentMethod,
        ?string $paypalCustomerId,
        ?string $paypalVaultId,
        bool $expressCheckout,
        bool $isUpdate,
        ?string $birthDate = null,
        ?string $phone = null
    ) {
        $this->cart = $cart;
        $this->fundingSource = $fundingSource;
        $this->savePaymentMethod = $savePaymentMethod;
        $this->paypalCustomerId = $paypalCustomerId;
        $this->paypalVaultId = $paypalVaultId;
        $this->expressCheckout = $expressCheckout;
        $this->isUpdate = $isUpdate;
        $this->birthDate = $birthDate;
        $this->phone = $phone;
    }

    public function getCart(): array
    {
        return $this->cart;
    }

    public function getFundingSource(): string
    {
        return $this->fundingSource;
    }

    public function isSavePaymentMethod(): bool
    {
        return $this->savePaymentMethod;
    }

    public function getPaypalCustomerId(): ?string
    {
        return $this->paypalCustomerId;
    }

    public function getPaypalVaultId(): ?string
    {
        return $this->paypalVaultId;
    }

    public function isExpressCheckout(): bool
    {
        return $this->expressCheckout;
    }

    public function isUpdate(): bool
    {
        return $this->isUpdate;
    }

    public function getBirthDate(): ?string
    {
        return $this->birthDate;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function getCartId(): int
    {
        return (int) ($this->cart['cart']['id'] ?? 0);
    }

    public function isVirtualCart(): bool
    {
        return (bool) ($this->cart['cart']['is_virtual'] ?? false);
    }

    public function hasShippingAddress(): bool
    {
        return isset($this->cart['addresses']['shipping'])
            && $this->cart['addresses']['shipping']->id !== null;
    }
}
