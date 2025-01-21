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
class PsCheckoutCart extends ObjectModel
{
    const STATUS_CREATED = 'CREATED';
    const STATUS_SAVED = 'SAVED';
    const STATUS_APPROVED = 'APPROVED';
    const STATUS_COMPLETED = 'COMPLETED';
    const STATUS_VOIDED = 'VOIDED';
    const STATUS_PAYER_ACTION_REQUIRED = 'PAYER_ACTION_REQUIRED';
    const STATUS_CANCELED = 'CANCELED';
    const STATUS_APPROVAL_REVERSED = 'REVERSED';
    const STATUS_PENDING_APPROVAL = 'PENDING_APPROVAL';
    const STATUS_PARTIALLY_COMPLETED = 'PARTIALLY_COMPLETED';

    /**
     * @var int|null Cart Identifier
     */
    public $id_cart;

    /**
     * @var string|null PayPal Order Intent
     */
    public $paypal_intent;

    /**
     * @var string|null PayPal Order Identifier
     */
    public $paypal_order;

    /**
     * @var string|null PayPal Order Status
     */
    public $paypal_status;

    /**
     * @var string|null PayPal Funding Source
     */
    public $paypal_funding;

    /**
     * @var string|null PayPal Access Token for Hosted-Fields
     */
    public $paypal_token;

    /**
     * @var string|null PayPal expiration date for Access Token
     */
    public $paypal_token_expire;

    /**
     * @var string|null PayPal expiration date for Authorization
     */
    public $paypal_authorization_expire;

    /**
     * @var string|null PayPal environment information
     */
    public $environment = 'LIVE';

    /**
     * @var bool
     */
    public $isExpressCheckout = false;

    /**
     * @var bool
     */
    public $isHostedFields = false;

    /**
     * @var string|null Creation date in mysql date format
     */
    public $date_add;

    /**
     * @var string|null Last modification date in mysql date format
     */
    public $date_upd;

    /**
     * @see ObjectModel::$definition
     */
    public static $definition = [
        'table' => 'pscheckout_cart',
        'primary' => 'id_pscheckout_cart',
        'fields' => [
            'id_cart' => [
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedInt',
                'required' => true,
            ],
            'paypal_intent' => [
                'type' => self::TYPE_STRING,
                'validate' => 'isGenericName',
                'size' => 20,
                'allow_null' => true,
                'required' => false,
            ],
            'paypal_order' => [
                'type' => self::TYPE_STRING,
                'validate' => 'isGenericName',
                'size' => 20,
                'allow_null' => true,
                'required' => false,
            ],
            'paypal_status' => [
                'type' => self::TYPE_STRING,
                'validate' => 'isGenericName',
                'size' => 30,
                'allow_null' => true,
                'required' => false,
            ],
            'paypal_funding' => [
                'type' => self::TYPE_STRING,
                'validate' => 'isGenericName',
                'size' => 20,
                'allow_null' => true,
                'required' => false,
            ],
            'paypal_token' => [
                'type' => self::TYPE_STRING,
                'allow_null' => true,
                'required' => false,
            ],
            'paypal_token_expire' => [
                'type' => self::TYPE_DATE,
                'validate' => 'isDate',
                'allow_null' => true,
                'required' => false,
            ],
            'paypal_authorization_expire' => [
                'type' => self::TYPE_DATE,
                'validate' => 'isDate',
                'allow_null' => true,
                'required' => false,
            ],
            'environment' => [
                'type' => self::TYPE_STRING,
                'allow_null' => true,
                'required' => false,
            ],
            'isExpressCheckout' => [
                'type' => self::TYPE_BOOL,
                'validate' => 'isBool',
                'allow_null' => true,
                'required' => false,
            ],
            'isHostedFields' => [
                'type' => self::TYPE_BOOL,
                'validate' => 'isBool',
                'allow_null' => true,
                'required' => false,
            ],
            'date_add' => [
                'type' => self::TYPE_DATE,
                'validate' => 'isDate',
            ],
            'date_upd' => [
                'type' => self::TYPE_DATE,
                'validate' => 'isDate',
            ],
        ],
    ];

    /**
     * @return int
     */
    public function getIdCart()
    {
        return (int) $this->id_cart;
    }

    /**
     * @return string|null
     */
    public function getPaypalIntent()
    {
        return $this->paypal_intent;
    }

    /**
     * @return string|null
     */
    public function getPaypalOrderId()
    {
        return $this->paypal_order;
    }

    /**
     * @return string|null
     */
    public function getPaypalStatus()
    {
        return $this->paypal_status;
    }

    /**
     * @return string|null
     */
    public function getPaypalFundingSource()
    {
        return $this->paypal_funding;
    }

    /**
     * @return string|null
     */
    public function getPaypalClientToken()
    {
        if (empty($this->paypal_token) || $this->isPaypalClientTokenExpired()) {
            return null;
        }

        return $this->paypal_token;
    }

    /**
     * @return bool
     */
    public function isPaypalClientTokenExpired()
    {
        if (empty($this->paypal_token_expire)) {
            return true;
        }

        try {
            return (new DateTime())->diff(new DateTime($this->paypal_token_expire))->invert === 1;
        } catch (Exception $e) {
            return true;
        }
    }

    /**
     * @return string
     */
    public function getPaypalAuthorizationExpireDate()
    {
        return $this->paypal_authorization_expire;
    }

    /**
     * @return string
     */
    public function getEnvironment()
    {
        return $this->environment === 'SANDBOX' ? $this->environment : 'LIVE';
    }

    /**
     * @return bool
     */
    public function isPaypalAuthorizationExpired()
    {
        if (empty($this->paypal_authorization_expire)) {
            return true;
        }

        try {
            return (new DateTime())->diff(new DateTime($this->paypal_authorization_expire))->invert === 1;
        } catch (Exception $e) {
            return true;
        }
    }

    /**
     * @return bool
     */
    public function isExpressCheckout()
    {
        return (bool) $this->isExpressCheckout;
    }

    /**
     * @return bool
     */
    public function isHostedFields()
    {
        return (bool) $this->isHostedFields;
    }

    /**
     * @return string|null
     */
    public function getDateAdd()
    {
        return $this->date_add;
    }

    /**
     * @return string|null
     */
    public function getDateUpd()
    {
        return $this->date_upd;
    }

    /**
     * @return bool
     */
    public function isPayPalOrderExpired()
    {
        return empty($this->date_add) || strtotime($this->date_add) + 3600 * 3 < time();
    }

    /**
     * @return bool
     */
    public function isOrderAvailable()
    {
        return $this->getPaypalOrderId()
            && in_array($this->paypal_status, [PsCheckoutCart::STATUS_CREATED, PsCheckoutCart::STATUS_APPROVED, PsCheckoutCart::STATUS_PAYER_ACTION_REQUIRED], true)
            && !$this->isPayPalOrderExpired();
    }
}
