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

namespace PsCheckout\Core\PaymentToken\ValueObject;

class PaymentToken
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var string
     */
    private $paypalCustomerId;

    /**
     * @var string
     */
    private $paymentSource;

    /**
     * @var array
     */
    private $data;

    /**
     * @var string
     */
    private $merchantId;

    /**
     * @var string
     */
    private $status;

    /**
     * @var bool
     */
    private $isFavorite;

    public function __construct(
        string $id,
        string $paypalCustomerId,
        string $paymentSource,
        array $data,
        string $merchantId,
        string $status,
        bool $isFavorite
    ) {
        $this->id = $id;
        $this->paypalCustomerId = $paypalCustomerId;
        $this->paymentSource = $paymentSource;
        $this->data = $data;
        $this->merchantId = $merchantId;
        $this->status = $status;
        $this->isFavorite = $isFavorite;
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getPaypalCustomerId(): string
    {
        return $this->paypalCustomerId;
    }

    /**
     * @return string
     */
    public function getPaymentSource(): string
    {
        return $this->paymentSource;
    }

    /**
     * @return array
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * @return string
     */
    public function getMerchantId(): string
    {
        return $this->merchantId;
    }

    /**
     * @return string
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * @return bool
     */
    public function isFavorite(): bool
    {
        return $this->isFavorite;
    }

    /**
     * @param array $tokenData
     *
     * @return PaymentToken
     */
    public static function createFromArray(array $tokenData): PaymentToken
    {
        return new self(
            $tokenData['token_id'],
            $tokenData['paypal_customer_id'],
            $tokenData['payment_source'],
            json_decode($tokenData['data'], true),
            $tokenData['merchant_id'],
            $tokenData['status'],
            (bool) $tokenData['is_favorite']
        );
    }
}
