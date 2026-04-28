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

namespace PsCheckout\Core\Order\Builder\Node\PaymentSource;

use PsCheckout\Infrastructure\Adapter\ConfigurationInterface;
use PsCheckout\Utility\Common\StringUtility;

class VenmoPaymentSourceNodeBuilder implements VenmoPaymentSourceNodeBuilderInterface
{
    /**
     * @var string
     */
    private $paypalVaultId;

    /**
     * @var string
     */
    private $paypalCustomerId;

    /**
     * @var bool
     */
    private $savePaymentMethod;

    /**
     * @var bool
     */
    private $isExpressCheckout = false;

    /**
     * @var array|null
     */
    private $cart;

    /**
     * @var ConfigurationInterface
     */
    private $configuration;

    public function __construct(ConfigurationInterface $configuration)
    {
        $this->configuration = $configuration;
    }

    /**
     * {@inheritDoc}
     */
    public function build(): array
    {
        $data = [];

        if ($this->cart !== null) {
            $data['email_address'] = (string) $this->cart['customer']->email;
        }

        if ($this->savePaymentMethod) {
            $data['attributes']['vault'] = [
                'store_in_vault' => 'ON_SUCCESS',
                'usage_pattern' => 'IMMEDIATE',
                'usage_type' => 'MERCHANT',
                'customer_type' => 'CONSUMER',
                'permit_multiple_payment_tokens' => false,
            ];
            if ($this->paypalCustomerId) {
                $data['attributes']['customer'] = [
                    'id' => $this->paypalCustomerId,
                ];
            }
        }

        if ($this->paypalVaultId) {
            $data['vault_id'] = $this->paypalVaultId;
        }

        $isVirtual = isset($this->cart['cart']['is_virtual']) && (bool) $this->cart['cart']['is_virtual'];
        $hasShipping = isset($this->cart['addresses']['shipping']) && $this->cart['addresses']['shipping']->id !== null;
        $data['experience_context'] = [
            'brand_name' => StringUtility::normalizeBrandName((string) $this->configuration->get('PS_SHOP_NAME')),
            'shipping_preference' => $isVirtual ? 'NO_SHIPPING' : ($hasShipping ? 'SET_PROVIDED_ADDRESS' : 'GET_FROM_FILE'),
            'user_action' => (!$this->isExpressCheckout && $this->cart !== null) ? 'PAY_NOW' : 'CONTINUE',
        ];

        return [
            'payment_source' => [
                'venmo' => $data,
            ],
        ];
    }

    /** {@inheritDoc} */
    public function setPaypalVaultId($paypalVaultId): self
    {
        $this->paypalVaultId = $paypalVaultId;

        return $this;
    }

    /** {@inheritDoc} */
    public function setPaypalCustomerId($paypalCustomerId): self
    {
        $this->paypalCustomerId = $paypalCustomerId;

        return $this;
    }

    /** {@inheritDoc} */
    public function setSavePaymentMethod(bool $savePaymentMethod): self
    {
        $this->savePaymentMethod = $savePaymentMethod;

        return $this;
    }

    /** {@inheritDoc} */
    public function setCart(array $cart)
    {
        $this->cart = $cart;

        return $this;
    }

    /** {@inheritDoc} */
    public function setIsExpressCheckout(bool $isExpressCheckout): self
    {
        $this->isExpressCheckout = $isExpressCheckout;

        return $this;
    }
}
