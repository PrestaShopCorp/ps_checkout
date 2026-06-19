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

namespace PsCheckout\Core\PayPal\ShippingCallback\ValueObject;

class ShippingCallbackPayload
{
    /**
     * @var string
     */
    private $paypalOrderId;

    /**
     * @var string
     */
    private $countryCode;

    /**
     * @var string
     */
    private $adminArea1;

    /**
     * @var string
     */
    private $adminArea2;

    /**
     * @var string
     */
    private $postalCode;

    /**
     * @var string|null null on SHIPPING_ADDRESS event, string on SHIPPING_OPTIONS event
     */
    private $shippingOptionId;

    /**
     * @var string
     */
    private $referenceId;

    public function __construct(array $data)
    {
        $this->paypalOrderId = (string) ($data['id'] ?? '');
        $this->countryCode = (string) ($data['shipping_address']['country_code'] ?? '');
        $this->adminArea1 = (string) ($data['shipping_address']['admin_area_1'] ?? '');
        $this->adminArea2 = (string) ($data['shipping_address']['admin_area_2'] ?? '');
        $this->postalCode = (string) ($data['shipping_address']['postal_code'] ?? '');
        $this->shippingOptionId = isset($data['shipping_option']['id'])
            ? (string) $data['shipping_option']['id']
            : null;
        $this->referenceId = (string) ($data['purchase_units'][0]['reference_id'] ?? 'default');
    }

    public function getPaypalOrderId(): string
    {
        return $this->paypalOrderId;
    }

    public function getCountryCode(): string
    {
        return $this->countryCode;
    }

    public function getAdminArea1(): string
    {
        return $this->adminArea1;
    }

    public function getAdminArea2(): string
    {
        return $this->adminArea2;
    }

    public function getPostalCode(): string
    {
        return $this->postalCode;
    }

    public function getShippingOptionId(): ?string
    {
        return $this->shippingOptionId;
    }

    public function getReferenceId(): string
    {
        return $this->referenceId;
    }

    public function isAddressEvent(): bool
    {
        return $this->shippingOptionId === null;
    }
}
