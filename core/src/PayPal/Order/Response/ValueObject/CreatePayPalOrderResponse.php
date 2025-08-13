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

namespace PsCheckout\Core\PayPal\Order\Response\ValueObject;

class CreatePayPalOrderResponse
{
    private $id;

    private $intent;

    private $status;

    private $purchaseUnits;

    private $payer;

    private $createTime;

    private $links;

    private $clientToken;

    private $paymentSource;

    public function __construct(
        string $id,
        string $intent,
        string $status,
        array $purchaseUnits,
        string $payer,
        string $createTime,
        array $links,
        string $clientToken,
        array $paymentSource
    ) {
        $this->id = $id;
        $this->intent = $intent;
        $this->status = $status;
        $this->purchaseUnits = $purchaseUnits;
        $this->payer = $payer;
        $this->createTime = $createTime;
        $this->links = $links;
        $this->clientToken = $clientToken;
        $this->paymentSource = $paymentSource;
    }

    /**
     * @param array $data
     *
     * @return self
     */
    public static function createFromResponse(array $data): self
    {
        return new self(
            $data['id'],
            $data['intent'] ?? '',
            $data['status'] ?? '',
            $data['purchase_units'] ?? [],
            $data['payer'] ?? '',
            $data['create_time'] ?? '',
            $data['links'] ?? [],
            $data['client_token'] ?? '',
            $data['payment_source'] ?? []
        );
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getIntent(): string
    {
        return $this->intent;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getPurchaseUnits(): array
    {
        return $this->purchaseUnits;
    }

    public function getPayer(): string
    {
        return $this->payer;
    }

    public function getCreateTime(): string
    {
        return $this->createTime;
    }

    public function getLinks(): array
    {
        return $this->links;
    }

    public function getClientToken(): string
    {
        return $this->clientToken;
    }

    public function getPaymentSource(): array
    {
        return $this->paymentSource;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'intent' => $this->intent,
            'status' => $this->status,
            'purchase_units' => $this->purchaseUnits,
            'payer' => $this->payer,
            'create_time' => $this->createTime,
            'links' => $this->links,
            'client_token' => $this->clientToken,
            'payment_source' => $this->paymentSource,
        ];
    }
}
