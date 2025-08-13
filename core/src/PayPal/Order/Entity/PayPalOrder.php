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

namespace PsCheckout\Core\PayPal\Order\Entity;

class PayPalOrder
{
    const CUSTOMER_INTENT_VAULT = 'VAULT';

    const CUSTOMER_INTENT_USES_VAULTING = 'USES_VAULTING';

    const THREE_D_SECURE_NOT_REQUIRED = '3DS_NOT_REQUIRED';

    /**
     * @var string
     */
    private $id;

    /**
     * @var int
     */
    private $idCart;

    /**
     * @var string
     */
    private $intent;

    /**
     * @var string
     */
    private $fundingSource;

    /**
     * @var string
     */
    private $status;

    /**
     * @var array
     */
    private $paymentSource;

    /**
     * @var string
     */
    private $environment;

    /**
     * @var bool
     */
    private $isCardFields;

    /**
     * @var bool
     */
    private $isExpressCheckout;

    /**
     * @var array
     */
    private $customerIntent;

    /**
     * @var string|null
     */
    private $paymentTokenId;

    /**
     * @var array
     */
    private $tags;

    /**
     * Constructor to initialize PayPalOrder properties
     */
    public function __construct(
        string $id,
        int $idCart,
        string $intent,
        string $fundingSource,
        string $status,
        array $paymentSource,
        string $environment,
        bool $isCardFields,
        bool $isExpressCheckout,
        array $customerIntent,
        $paymentTokenId = null,
        $tags = []
    ) {
        $this->id = $id;
        $this->idCart = $idCart;
        $this->intent = $intent;
        $this->fundingSource = $fundingSource;
        $this->status = $status;
        $this->paymentSource = $paymentSource;
        $this->environment = $environment;
        $this->isCardFields = $isCardFields;
        $this->isExpressCheckout = $isExpressCheckout;
        $this->customerIntent = $customerIntent;
        $this->paymentTokenId = $paymentTokenId;
        $this->tags = $tags;
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @return int
     */
    public function getIdCart(): int
    {
        return $this->idCart;
    }

    /**
     * @return string
     */
    public function getIntent(): string
    {
        return $this->intent;
    }

    /**
     * @return string
     */
    public function getFundingSource(): string
    {
        return $this->fundingSource;
    }

    /**
     * @return string
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * @return array
     */
    public function getPaymentSource(): array
    {
        return $this->paymentSource;
    }

    /**
     * @return string
     */
    public function getEnvironment(): string
    {
        return $this->environment;
    }

    /**
     * @return bool
     */
    public function isCardFields(): bool
    {
        return $this->isCardFields;
    }

    /**
     * @return bool
     */
    public function isExpressCheckout(): bool
    {
        return $this->isExpressCheckout;
    }

    /**
     * @return array
     */
    public function getCustomerIntent(): array
    {
        return $this->customerIntent;
    }

    /**
     * The customer intent, which can be one of the following constants:
     * - PayPalConfiguration::PS_CHECKOUT_CUSTOMER_INTENT_VAULT
     * - PayPalConfiguration::PS_CHECKOUT_CUSTOMER_INTENT_FAVORITE
     * - PayPalConfiguration::PS_CHECKOUT_CUSTOMER_INTENT_USES_VAULTING
     *
     * @param string $intent
     *
     * @return bool
     */
    public function checkCustomerIntent(string $intent): bool
    {
        return in_array($intent, $this->customerIntent);
    }

    /**
     * @return string|null
     */
    public function getPaymentTokenId()
    {
        return $this->paymentTokenId;
    }

    /**
     * @param int $idCart
     *
     * @return $this
     */
    public function setIdCart(int $idCart): self
    {
        $this->idCart = $idCart;

        return $this;
    }

    /**
     * @param string $intent
     *
     * @return self
     */
    public function setIntent(string $intent): self
    {
        $this->intent = $intent;

        return $this;
    }

    /**
     * @param array $customerIntent
     *
     * @return self
     */
    public function setCustomerIntent(array $customerIntent): self
    {
        $this->customerIntent = $customerIntent;

        return $this;
    }

    /**
     * @param string $fundingSource
     *
     * @return self
     */
    public function setFundingSource(string $fundingSource): self
    {
        $this->fundingSource = $fundingSource;

        return $this;
    }

    /**
     * @param string $status
     *
     * @return self
     */
    public function setStatus(string $status): self
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @param array $paymentSource
     *
     * @return self
     */
    public function setPaymentSource(array $paymentSource): self
    {
        $this->paymentSource = $paymentSource;

        return $this;
    }

    /**
     * @param string $environment
     *
     * @return self
     */
    public function setEnvironment(string $environment): self
    {
        $this->environment = $environment;

        return $this;
    }

    /**
     * @param bool $isCardFields
     *
     * @return self
     */
    public function setIsCardFields(bool $isCardFields): self
    {
        $this->isCardFields = $isCardFields;

        return $this;
    }

    /**
     * @param bool $isExpressCheckout
     *
     * @return self
     */
    public function setIsExpressCheckout(bool $isExpressCheckout): self
    {
        $this->isExpressCheckout = $isExpressCheckout;

        return $this;
    }

    /**
     * @param string|null $paymentTokenId
     *
     * @return self
     */
    public function setPaymentTokenId($paymentTokenId): self
    {
        $this->paymentTokenId = $paymentTokenId;

        return $this;
    }

    /**
     * @return string[]
     */
    public function getTags()
    {
        return $this->tags;
    }

    /**
     * @param array $tags
     *
     * @return void
     */
    public function setTags(array $tags)
    {
        $this->tags = $tags;
    }

    /**
     * @param string $tag
     *
     * @return void
     */
    public function addTag($tag)
    {
        $this->tags[] = $tag;
    }
}
