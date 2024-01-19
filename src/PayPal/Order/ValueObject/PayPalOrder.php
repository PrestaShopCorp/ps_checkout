<?php

namespace PrestaShop\Module\PrestashopCheckout\PayPal\Order\ValueObject;

use PrestaShop\Module\PrestashopCheckout\Order\ValueObject\OrderId;

class PayPalOrder
{
    /**
     * @var PayPalOrderId|null
     */
    private $id;
    /**
     * @var string|null
     */
    private $status;
    /**
     * @var string|null
     */
    private $intent;
    /**
     * @var PayPalOrderPaymentSource|null
     */
    private $paymentSource;
    /**
     * @var PayPalOrderPurchaseUnit[]
     */
    private $purchaseUnits;
    /**
     * @var array|null
     */
    private $payer;
    /**
     * @var string|null
     */
    private $createTime;
    /**
     * @var array|null
     */
    private $links;

    public function __construct(PayPalOrderId $id = null, $status = null, $intent = null, PayPalOrderPaymentSource $paymentSource = null, $purchaseUnits = [], $payer = [], $createTime = null, $links = [] )
    {
        $this->id = $id;
        $this->status = $status;
        $this->intent = $intent;
        $this->paymentSource = $paymentSource;
        $this->purchaseUnits = $purchaseUnits;
        $this->payer = $payer;
        $this->createTime = $createTime;
        $this->links = $links;
    }

    /**
     * @return PayPalOrderId|null
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param OrderId $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param string $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * @return string
     */
    public function getIntent()
    {
        return $this->intent;
    }

    /**
     * @param string $intent
     */
    public function setIntent($intent)
    {
        $this->intent = $intent;
    }

    /**
     * @return PayPalOrderPaymentSource
     */
    public function getPaymentSource()
    {
        return $this->paymentSource;
    }

    /**
     * @param PayPalOrderPaymentSource $paymentSource
     */
    public function setPaymentSource($paymentSource)
    {
        $this->paymentSource = $paymentSource;
    }

    /**
     * @return array
     */
    public function getPurchaseUnits()
    {
        return $this->purchaseUnits;
    }

    /**
     * @param PayPalOrderPurchaseUnit[] $purchaseUnits
     */
    public function setPurchaseUnits($purchaseUnits)
    {
        $this->purchaseUnits = $purchaseUnits;
    }

    /**
     * @return array
     */
    public function getPayer()
    {
        return $this->payer;
    }

    /**
     * @param array $payer
     */
    public function setPayer($payer)
    {
        $this->payer = $payer;
    }

    /**
     * @return string
     */
    public function getCreateTime()
    {
        return $this->createTime;
    }

    /**
     * @param string $createTime
     */
    public function setCreateTime($createTime)
    {
        $this->createTime = $createTime;
    }

    /**
     * @return array
     */
    public function getLinks()
    {
        return $this->links;
    }

    /**
     * @param array $links
     */
    public function setLinks($links)
    {
        $this->links = $links;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            'id' => $this->id ? $this->id->getValue() : null,
            'status' => $this->status,
            'intent' => $this->intent,
            'payment_source' => $this->paymentSource === null ? null : $this->paymentSource->toArray(),
            'purchase_units' => array_map(function(PayPalOrderPurchaseUnit $purchaseUnit) {
                return $purchaseUnit->toArray();
            }, $this->purchaseUnits),
            'payer' => $this->payer,
            'create_time' => $this->createTime,
            'links' => $this->links
        ];
    }
}
