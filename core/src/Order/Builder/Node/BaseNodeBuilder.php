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

namespace PsCheckout\Core\Order\Builder\Node;

use PsCheckout\Core\Settings\Configuration\PayPalConfiguration;
use PsCheckout\Infrastructure\Adapter\ConfigurationInterface;
use PsCheckout\Utility\Common\NumberUtility;
use PsCheckout\Utility\Common\StringUtility;

class BaseNodeBuilder implements BaseNodeBuilderInterface
{
    /**
     * @var ConfigurationInterface
     */
    private $configuration;

    /**
     * @var array
     */
    private $cart;

    /**
     * @var bool
     */
    private $isVault;

    /**
     * @var bool
     */
    private $isUpdate;

    /**
     * @var string
     */
    private $paypalOrderId;

    public function __construct(
        ConfigurationInterface $configuration
    ) {
        $this->configuration = $configuration;
    }

    /**
     * {@inheritDoc}
     */
    public function build(): array
    {
        $shopName = $this->configuration->get('PS_SHOP_NAME');
        $merchantId = $this->configuration->get(PayPalConfiguration::PS_CHECKOUT_PAYPAL_ID_MERCHANT);

        $node = [
            'intent' => $this->configuration->get(PayPalConfiguration::PS_CHECKOUT_INTENT),
            'custom_id' => (string) $this->cart['cart']['id'],
            'invoice_id' => '',
            'description' => StringUtility::truncate(
                'Checking out with your cart #' . $this->cart['cart']['id'] . ' from ' . $shopName,
                127
            ),
            'amount' => [
                'currency_code' => $this->cart['currency']['iso_code'],
                'value' => NumberUtility::formatAmount($this->cart['cart']['totals']['total_including_tax']['amount'], $this->cart['currency']['iso_code']),
            ],
            'payee' => [
                'merchant_id' => $merchantId,
            ],
            'vault' => $this->isVault,
        ];

        if ($this->isUpdate) {
            $node['id'] = $this->paypalOrderId;
        } else {
            $roundType = $this->configuration->get(PayPalConfiguration::PS_ROUND_TYPE);
            $roundMode = $this->configuration->get(PayPalConfiguration::PS_PRICE_ROUND_MODE);
            $node['roundingConfig'] = $roundType . '-' . $roundMode;
        }

        return $node;
    }

    /**
     * {@inheritdoc}
     */
    public function setCart(array $cart): self
    {
        $this->cart = $cart;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setIsVault(bool $isVault): self
    {
        $this->isVault = $isVault;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function setIsUpdate(bool $isUpdate): self
    {
        $this->isUpdate = $isUpdate;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function setPaypalOrderId($paypalOrderId): self
    {
        $this->paypalOrderId = $paypalOrderId;

        return $this;
    }
}
