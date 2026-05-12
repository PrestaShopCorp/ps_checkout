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

namespace PsCheckout\Infrastructure\Builder;

use PsCheckout\Core\Order\Builder\CheckoutContext;
use PsCheckout\Core\Order\Builder\CheckoutContextBuilderInterface;
use PsCheckout\Core\Order\Builder\CheckoutContextInterface;
use PsCheckout\Core\PayPal\Customer\Repository\PayPalCustomerRepositoryInterface;
use PsCheckout\Core\PayPal\ShippingCallback\Builder\ShippingOptionsBuilderInterface;
use PsCheckout\Infrastructure\Adapter\ContextInterface;
use PsCheckout\Presentation\Presenter\PresenterInterface;

class CheckoutContextBuilder implements CheckoutContextBuilderInterface
{
    private static $shippingEligibleSources = ['paypal', 'paylater', 'credit', 'venmo'];

    /** @var PresenterInterface */
    private $cartPresenter;

    /** @var PayPalCustomerRepositoryInterface */
    private $payPalCustomerRepository;

    /** @var ShippingOptionsBuilderInterface */
    private $shippingOptionsBuilder;

    /** @var ContextInterface */
    private $context;

    /** @var string */
    private $fundingSource = '';

    /** @var bool */
    private $isExpressCheckout = false;

    /** @var bool */
    private $savePaymentMethod = false;

    /** @var bool */
    private $isCard = false;

    /** @var bool */
    private $isVault = false;

    /** @var string|null */
    private $paypalVaultId = null;

    /** @var string|null */
    private $paypalOrderId = null;

    /** @var bool */
    private $isUpdate = false;

    /** @var string|null */
    private $birthDate = null;

    /** @var string|null */
    private $phone = null;

    public function __construct(
        PresenterInterface $cartPresenter,
        PayPalCustomerRepositoryInterface $payPalCustomerRepository,
        ShippingOptionsBuilderInterface $shippingOptionsBuilder,
        ContextInterface $context
    ) {
        $this->cartPresenter = $cartPresenter;
        $this->payPalCustomerRepository = $payPalCustomerRepository;
        $this->shippingOptionsBuilder = $shippingOptionsBuilder;
        $this->context = $context;
    }

    public function setFundingSource(string $fundingSource): CheckoutContextBuilderInterface
    {
        $this->fundingSource = $fundingSource;

        return $this;
    }

    public function setIsExpressCheckout(bool $isExpressCheckout): CheckoutContextBuilderInterface
    {
        $this->isExpressCheckout = $isExpressCheckout;

        return $this;
    }

    public function setSavePaymentMethod(bool $savePaymentMethod): CheckoutContextBuilderInterface
    {
        $this->savePaymentMethod = $savePaymentMethod;

        return $this;
    }

    public function setIsCard(bool $isCard): CheckoutContextBuilderInterface
    {
        $this->isCard = $isCard;

        return $this;
    }

    public function setIsVault(bool $isVault): CheckoutContextBuilderInterface
    {
        $this->isVault = $isVault;

        return $this;
    }

    public function setPaypalVaultId(string $paypalVaultId): CheckoutContextBuilderInterface
    {
        $this->paypalVaultId = $paypalVaultId;

        return $this;
    }

    public function setPaypalOrderId(string $paypalOrderId): CheckoutContextBuilderInterface
    {
        $this->paypalOrderId = $paypalOrderId;

        return $this;
    }

    public function setIsUpdate(bool $isUpdate): CheckoutContextBuilderInterface
    {
        $this->isUpdate = $isUpdate;

        return $this;
    }

    public function setBirthDate(?string $birthDate): CheckoutContextBuilderInterface
    {
        $this->birthDate = $birthDate;

        return $this;
    }

    public function setPhone(?string $phone): CheckoutContextBuilderInterface
    {
        $this->phone = $phone;

        return $this;
    }

    public function build(): CheckoutContextInterface
    {
        $fundingSource = $this->fundingSource;
        $isExpressCheckout = $this->isExpressCheckout;
        $savePaymentMethod = $this->savePaymentMethod;
        $isCard = $this->isCard;
        $isVault = $this->isVault;
        $paypalVaultId = $this->paypalVaultId;
        $paypalOrderId = $this->paypalOrderId;
        $isUpdate = $this->isUpdate;
        $birthDate = $this->birthDate;
        $phone = $this->phone;

        $this->fundingSource = '';
        $this->isExpressCheckout = false;
        $this->savePaymentMethod = false;
        $this->isCard = false;
        $this->isVault = false;
        $this->paypalVaultId = null;
        $this->paypalOrderId = null;
        $this->isUpdate = false;
        $this->birthDate = null;
        $this->phone = null;

        $cart = $this->cartPresenter->present();
        $cartId = (int) ($cart['cart']['id'] ?? 0);

        $paypalCustomerId = null;
        $customer = $this->context->getCustomer();
        if ($customer && $customer->id) {
            $paypalCustomerId = $this->payPalCustomerRepository->getPayPalCustomerIdByCustomerId($customer->id);
        }

        $shippingOptions = [];
        $isVirtualCart = (bool) ($cart['cart']['is_virtual'] ?? false);

        if (!$isUpdate && !$isCard && !$isVirtualCart && in_array($fundingSource, self::$shippingEligibleSources, true)) {
            $shippingOptions = $this->shippingOptionsBuilder->build($cartId, null);
        }

        return new CheckoutContext(
            $cart,
            $fundingSource,
            $savePaymentMethod,
            $paypalCustomerId,
            $paypalVaultId,
            $isExpressCheckout,
            $isUpdate,
            $birthDate,
            $phone,
            $isCard,
            $isVault,
            $paypalOrderId,
            $shippingOptions
        );
    }
}
