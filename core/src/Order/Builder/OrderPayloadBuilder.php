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
use PsCheckout\Core\Order\Builder\Node\AmountBreakdownNodeInterface;
use PsCheckout\Core\Order\Builder\Node\BaseNodeBuilderInterface;
use PsCheckout\Core\Order\Builder\Node\CardPaymentSourceNodeBuilderInterface;
use PsCheckout\Core\Order\Builder\Node\GooglePayPaymentSourceNodeBuilderInterface;
use PsCheckout\Core\Order\Builder\Node\PaymentSource\ApmPaymentSourceNodeBuilderInterface;
use PsCheckout\Core\Order\Builder\Node\PaymentSource\ApplePayPaymentSourceNodeBuilderInterface;
use PsCheckout\Core\Order\Builder\Node\PaymentSource\VenmoPaymentSourceNodeBuilderInterface;
use PsCheckout\Core\Order\Builder\Node\PayPalPaymentSourceNodeBuilderInterface;
use PsCheckout\Core\Order\Builder\Node\PuiPaymentSourceNodeBuilderInterface;
use PsCheckout\Core\Order\Builder\Node\ShippingNodeBuilderInterface;
use PsCheckout\Core\Order\Builder\Node\SupplementaryDataNodeBuilderInterface;

class OrderPayloadBuilder implements OrderPayloadBuilderInterface
{
    /** @var array<string, mixed> */
    private $cart;

    /** @var string */
    private $fundingSource;

    /** @var string */
    private $paypalOrderId;

    /** @var string */
    private $paypalCustomerId;

    /** @var string */
    private $paypalVaultId;

    /** @var bool */
    private $savePaymentMethod = false;

    /** @var bool */
    private $isUpdate = false;

    /** @var bool */
    private $expressCheckout = false;

    /** @var bool */
    private $isVault = false;

    /** @var bool */
    private $isCard = false;

    /** @var array */
    private $payload = [];

    /** @var BaseNodeBuilderInterface */
    private $baseNodeBuilder;

    /** @var AmountBreakdownNodeInterface */
    private $amountBreakdownNodeBuilder;

    /** @var ShippingNodeBuilderInterface */
    private $shippingNodeBuilder;

    /** @var CardPaymentSourceNodeBuilderInterface */
    private $cardPaymentSourceNodeBuilder;

    /** @var SupplementaryDataNodeBuilderInterface */
    private $supplementaryDataNodeBuilder;

    /** @var PayPalPaymentSourceNodeBuilderInterface */
    private $payPalPaymentSourceNodeBuilder;

    /** @var GooglePayPaymentSourceNodeBuilderInterface */
    private $googlePayPaymentSourceNodeBuilder;

    /** @var VenmoPaymentSourceNodeBuilderInterface */
    private $venmoPaymentSourceNodeBuilder;

    /** @var PuiPaymentSourceNodeBuilderInterface */
    private $puiPaymentSourceNodeBuilder;

    /** @var ApmPaymentSourceNodeBuilderInterface */
    private $idealPaymentSourceNodeBuilder;

    /** @var ApmPaymentSourceNodeBuilderInterface */
    private $mybankPaymentSourceNodeBuilder;

    /** @var ApmPaymentSourceNodeBuilderInterface */
    private $epsPaymentSourceNodeBuilder;

    /** @var ApmPaymentSourceNodeBuilderInterface */
    private $p24PaymentSourceNodeBuilder;

    /** @var ApmPaymentSourceNodeBuilderInterface */
    private $blikPaymentSourceNodeBuilder;

    /** @var ApmPaymentSourceNodeBuilderInterface */
    private $bancontactPaymentSourceNodeBuilder;

    /** @var ApplePayPaymentSourceNodeBuilderInterface */
    private $applePayPaymentSourceNodeBuilder;

    /** @var ?string */
    private $birthDate;

    /** @var ?string */
    private $phone;

    public function __construct(
        BaseNodeBuilderInterface $baseNodeBuilder,
        AmountBreakdownNodeInterface $amountBreakdownNodeBuilder,
        ShippingNodeBuilderInterface $shippingNodeBuilder,
        CardPaymentSourceNodeBuilderInterface $cardPaymentSourceNodeBuilder,
        SupplementaryDataNodeBuilderInterface $supplementaryDataNodeBuilder,
        PayPalPaymentSourceNodeBuilderInterface $payPalPaymentSourceNodeBuilder,
        GooglePayPaymentSourceNodeBuilderInterface $googlePayPaymentSourceNodeBuilder,
        VenmoPaymentSourceNodeBuilderInterface $venmoPaymentSourceNodeBuilder,
        PuiPaymentSourceNodeBuilderInterface $puiPaymentSourceNodeBuilder,
        ApmPaymentSourceNodeBuilderInterface $idealPaymentSourceNodeBuilder,
        ApmPaymentSourceNodeBuilderInterface $mybankPaymentSourceNodeBuilder,
        ApmPaymentSourceNodeBuilderInterface $epsPaymentSourceNodeBuilder,
        ApmPaymentSourceNodeBuilderInterface $p24PaymentSourceNodeBuilder,
        ApmPaymentSourceNodeBuilderInterface $blikPaymentSourceNodeBuilder,
        ApmPaymentSourceNodeBuilderInterface $bancontactPaymentSourceNodeBuilder,
        ApplePayPaymentSourceNodeBuilderInterface $applePayPaymentSourceNodeBuilder
    ) {
        $this->baseNodeBuilder = $baseNodeBuilder;
        $this->amountBreakdownNodeBuilder = $amountBreakdownNodeBuilder;
        $this->shippingNodeBuilder = $shippingNodeBuilder;
        $this->cardPaymentSourceNodeBuilder = $cardPaymentSourceNodeBuilder;
        $this->supplementaryDataNodeBuilder = $supplementaryDataNodeBuilder;
        $this->payPalPaymentSourceNodeBuilder = $payPalPaymentSourceNodeBuilder;
        $this->googlePayPaymentSourceNodeBuilder = $googlePayPaymentSourceNodeBuilder;
        $this->venmoPaymentSourceNodeBuilder = $venmoPaymentSourceNodeBuilder;
        $this->puiPaymentSourceNodeBuilder = $puiPaymentSourceNodeBuilder;
        $this->idealPaymentSourceNodeBuilder = $idealPaymentSourceNodeBuilder;
        $this->mybankPaymentSourceNodeBuilder = $mybankPaymentSourceNodeBuilder;
        $this->epsPaymentSourceNodeBuilder = $epsPaymentSourceNodeBuilder;
        $this->p24PaymentSourceNodeBuilder = $p24PaymentSourceNodeBuilder;
        $this->blikPaymentSourceNodeBuilder = $blikPaymentSourceNodeBuilder;
        $this->bancontactPaymentSourceNodeBuilder = $bancontactPaymentSourceNodeBuilder;
        $this->applePayPaymentSourceNodeBuilder = $applePayPaymentSourceNodeBuilder;
    }

    /** {@inheritDoc} */
    public function build(bool $isFullPayload = true): array
    {
        $this->checkPaypalOrderIdWhenUpdate();

        // Build the base payload
        $this->payload = $this->buildBasePayload();

        // Prepare optional payload elements
        $optionalPayload = $this->buildOptionalPayload($isFullPayload);

        // Merge the optional payload elements into the main payload
        $this->mergePayload($optionalPayload);

        return $this->payload;
    }

    /**
     * Builds the base payload using initial settings.
     *
     * @return array the base payload array
     */
    private function buildBasePayload(): array
    {
        return $this->baseNodeBuilder
            ->setCart($this->cart)
            ->setIsVault($this->isVault)
            ->setIsUpdate($this->isUpdate)
            ->setPaypalOrderId($this->paypalOrderId)
            ->build();
    }

    /**
     * Builds the optional payload elements based on various conditions.
     *
     * @param bool $isFullPayload whether to include the full payload or not
     *
     * @return array the optional payload elements
     */
    private function buildOptionalPayload(bool $isFullPayload): array
    {
        $optionalPayload = [];

        if ($isFullPayload) {
            $amountBreakdown = $this->amountBreakdownNodeBuilder
                ->setCart($this->cart)
                ->setFundingSource($this->fundingSource)
                ->build();
            if (!empty($amountBreakdown)) {
                $this->payload['purchase_units'][0] = array_replace_recursive($this->payload['purchase_units'][0], $amountBreakdown);
            }
        }

        if ($this->shippingAddressExists()) {
            $this->payload['purchase_units'][0] = array_merge($this->payload['purchase_units'][0], $this->shippingNodeBuilder->setCart($this->cart)->build());
        }

        if ($this->isCard) {
            $optionalPayload[] = $this->buildCardPaymentSource();
            $this->payload['purchase_units'][0] = array_merge($this->payload['purchase_units'][0], $this->buildSupplementaryData());
        }

        if ($isFullPayload) {
            $paymentSource = $this->buildPaymentSource();

            if (!empty($paymentSource)) {
                $optionalPayload[] = $paymentSource;
            }
        }

        if ($this->fundingSource === 'pay_upon_invoice' && !$this->isUpdate) {
            $optionalPayload[] = ['processing_instruction' => 'ORDER_COMPLETE_ON_PAYMENT_APPROVAL'];
        }

        return $optionalPayload;
    }

    /**
     * Builds the card payment source payload element.
     *
     * @return array the card payment source payload
     */
    private function buildCardPaymentSource(): array
    {
        return $this->cardPaymentSourceNodeBuilder
            ->setCart($this->cart)
            ->setPaypalVaultId($this->paypalVaultId)
            ->setPaypalCustomerId($this->paypalCustomerId)
            ->setSavePaymentMethod($this->savePaymentMethod)
            ->build();
    }

    /**
     * Builds the supplementary data payload element.
     *
     * @return array the supplementary data payload
     */
    private function buildSupplementaryData(): array
    {
        return $this->supplementaryDataNodeBuilder
            ->setCart($this->cart)
            ->setPayload($this->payload)
            ->build();
    }

    /**
     * Merges optional payload elements into the main payload.
     *
     * @param array $optionalPayload the optional payload elements to merge
     */
    private function mergePayload(array $optionalPayload)
    {
        foreach ($optionalPayload as $node) {
            if (!empty($node)) {
                $this->payload = array_replace_recursive($this->payload, $node);
            }
        }
    }

    /**
     * Ensures PayPal order ID is set when updating.
     *
     * @throws PsCheckoutException
     */
    private function checkPaypalOrderIdWhenUpdate()
    {
        if ($this->isUpdate && empty($this->paypalOrderId)) {
            throw new PsCheckoutException('PayPal order ID is required when building payload for updating an order');
        }
    }

    /**
     * Builds the payment source node based on the funding source.
     *
     * @return array|null the payment source node
     */
    private function buildPaymentSource()
    {
        switch ($this->fundingSource) {
            case 'google_pay':
                return $this->googlePayPaymentSourceNodeBuilder->build();
            case 'paypal':
            case 'paylater':
            case 'credit':
                $paypalBuilder = $this->payPalPaymentSourceNodeBuilder
                    ->setSavePaymentMethod($this->savePaymentMethod)
                    ->setPaypalCustomerId($this->paypalCustomerId)
                    ->setPaypalVaultId($this->paypalVaultId)
                    ->setShippingAddressExists($this->shippingAddressExists())
                    ->setVirtualCart((bool) $this->cart['cart']['is_virtual'])
                    ->setIsExpressCheckout($this->expressCheckout)
                    ->setFundingSource($this->fundingSource);

                if (!$this->expressCheckout && !$this->isUpdate) {
                    $paypalBuilder->setCart($this->cart);
                }

                return $paypalBuilder->build();
            case 'venmo':
                $venmoBuilder = $this->venmoPaymentSourceNodeBuilder
                    ->setSavePaymentMethod($this->savePaymentMethod)
                    ->setPaypalCustomerId($this->paypalCustomerId)
                    ->setPaypalVaultId($this->paypalVaultId)
                    ->setIsExpressCheckout($this->expressCheckout);

                if (!$this->expressCheckout && !$this->isUpdate) {
                    $venmoBuilder->setCart($this->cart);
                }

                return $venmoBuilder->build();
            case 'pay_upon_invoice':
                return $this->puiPaymentSourceNodeBuilder->setCart($this->cart)
                    ->setBirthDate($this->birthDate)
                    ->setPhone($this->phone)
                    ->build();
            case 'ideal':
                return $this->idealPaymentSourceNodeBuilder->setCart($this->cart)->build();
            case 'mybank':
                return $this->mybankPaymentSourceNodeBuilder->setCart($this->cart)->build();
            case 'eps':
                return $this->epsPaymentSourceNodeBuilder->setCart($this->cart)->build();
            case 'p24':
                return $this->p24PaymentSourceNodeBuilder->setCart($this->cart)->build();
            case 'blik':
                return $this->blikPaymentSourceNodeBuilder->setCart($this->cart)->build();
            case 'bancontact':
                return $this->bancontactPaymentSourceNodeBuilder->setCart($this->cart)->build();
            case 'apple_pay':
                return $this->applePayPaymentSourceNodeBuilder->build();
        }

        return null;
    }

    /** {@inheritDoc} */
    public function setCart(array $cart): self
    {
        $this->cart = $cart;

        return $this;
    }

    /** {@inheritDoc} */
    public function setIsUpdate(bool $isUpdate): self
    {
        $this->isUpdate = $isUpdate;

        return $this;
    }

    /** {@inheritDoc} */
    public function setIsExpressCheckout(bool $isExpressCheckout): self
    {
        $this->expressCheckout = $isExpressCheckout;

        return $this;
    }

    /** {@inheritDoc} */
    public function setSavePaymentMethod(bool $savePaymentMethod): self
    {
        $this->savePaymentMethod = $savePaymentMethod;

        return $this;
    }

    /** {@inheritDoc} */
    public function setFundingSource(string $fundingSource): self
    {
        $this->fundingSource = $fundingSource;

        return $this;
    }

    /** {@inheritDoc} */
    public function setPaypalCustomerId(string $paypalCustomerId): self
    {
        $this->paypalCustomerId = $paypalCustomerId;

        return $this;
    }

    /** {@inheritDoc} */
    public function setPaypalVaultId(string $paypalVaultId): self
    {
        $this->paypalVaultId = $paypalVaultId;

        return $this;
    }

    /** {@inheritDoc} */
    public function setPaypalOrderId(string $paypalOrderId): self
    {
        $this->paypalOrderId = $paypalOrderId;

        return $this;
    }

    /** {@inheritDoc} */
    public function setIsVault(bool $isVault): self
    {
        $this->isVault = $isVault;

        return $this;
    }

    /** {@inheritDoc} */
    public function setIsCard(bool $isCard): self
    {
        $this->isCard = $isCard;

        return $this;
    }

    private function shippingAddressExists(): bool
    {
        if (isset($this->cart['addresses']['shipping'])) {
            return $this->cart['addresses']['shipping']->id !== null;
        }

        return false;
    }

    /** {@inheritDoc} */
    public function setCustomerBirthDay($birthDate): self
    {
        $this->birthDate = $birthDate;

        return $this;
    }

    /** {@inheritDoc} */
    public function setCustomerPhone($phone): self
    {
        $this->phone = $phone;

        return $this;
    }
}
