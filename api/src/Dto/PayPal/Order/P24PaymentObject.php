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

namespace PsCheckout\Api\Dto\PayPal\Order;

/**
 * Information used to pay using P24(Przelewy24).
 */
class P24PaymentObject
{
    /**
     * @var string|null
     */
    private $name;

    /**
     * @var string|null
     */
    private $email;

    /**
     * @var string|null
     */
    private $countryCode;

    /**
     * @var string|null
     */
    private $paymentDescriptor;

    /**
     * @var string|null
     */
    private $methodId;

    /**
     * @var string|null
     */
    private $methodDescription;

    /**
     * Returns Name.
     * The full name representation like Mr J Smith.
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * Sets Name.
     * The full name representation like Mr J Smith.
     *
     * @maps name
     */
    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    /**
     * Returns Email.
     * The internationalized email address. Note: Up to 64 characters are allowed before and 255 characters
     * are allowed after the @ sign. However, the generally accepted maximum length for an email address is
     * 254 characters. The pattern verifies that an unquoted @ sign exists.
     */
    public function getEmail(): ?string
    {
        return $this->email;
    }

    /**
     * Sets Email.
     * The internationalized email address. Note: Up to 64 characters are allowed before and 255 characters
     * are allowed after the @ sign. However, the generally accepted maximum length for an email address is
     * 254 characters. The pattern verifies that an unquoted @ sign exists.
     *
     * @maps email
     */
    public function setEmail(?string $email): void
    {
        $this->email = $email;
    }

    /**
     * Returns Country Code.
     * The [two-character ISO 3166-1 code](/api/rest/reference/country-codes/) that identifies the country
     * or region. Note: The country code for Great Britain is GB and not UK as used in the top-level domain
     * names for that country. Use the `C2` country code for China worldwide for comparable uncontrolled
     * price (CUP) method, bank card, and cross-border transactions.
     */
    public function getCountryCode(): ?string
    {
        return $this->countryCode;
    }

    /**
     * Sets Country Code.
     * The [two-character ISO 3166-1 code](/api/rest/reference/country-codes/) that identifies the country
     * or region. Note: The country code for Great Britain is GB and not UK as used in the top-level domain
     * names for that country. Use the `C2` country code for China worldwide for comparable uncontrolled
     * price (CUP) method, bank card, and cross-border transactions.
     *
     * @maps country_code
     */
    public function setCountryCode(?string $countryCode): void
    {
        $this->countryCode = $countryCode;
    }

    /**
     * Returns Payment Descriptor.
     * P24 generated payment description.
     */
    public function getPaymentDescriptor(): ?string
    {
        return $this->paymentDescriptor;
    }

    /**
     * Sets Payment Descriptor.
     * P24 generated payment description.
     *
     * @maps payment_descriptor
     */
    public function setPaymentDescriptor(?string $paymentDescriptor): void
    {
        $this->paymentDescriptor = $paymentDescriptor;
    }

    /**
     * Returns Method Id.
     * Numeric identifier of the payment scheme or bank used for the payment.
     */
    public function getMethodId(): ?string
    {
        return $this->methodId;
    }

    /**
     * Sets Method Id.
     * Numeric identifier of the payment scheme or bank used for the payment.
     *
     * @maps method_id
     */
    public function setMethodId(?string $methodId): void
    {
        $this->methodId = $methodId;
    }

    /**
     * Returns Method Description.
     * Friendly name of the payment scheme or bank used for the payment.
     */
    public function getMethodDescription(): ?string
    {
        return $this->methodDescription;
    }

    /**
     * Sets Method Description.
     * Friendly name of the payment scheme or bank used for the payment.
     *
     * @maps method_description
     */
    public function setMethodDescription(?string $methodDescription): void
    {
        $this->methodDescription = $methodDescription;
    }
}
