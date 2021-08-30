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

    /**
     * @var int Cart Identifier
     */
    public $id_cart;

    /**
     * @var string PayPal Order Intent
     */
    public $paypal_intent;

    /**
     * @var string PayPal Order Identifier
     */
    public $paypal_order;

    /**
     * @var string PayPal Order Status
     */
    public $paypal_status;

    /**
     * @var string PayPal Funding Source
     */
    public $paypal_funding;

    /**
     * @var string PayPal Access Token for Hosted-Fields
     */
    public $paypal_token;

    /**
     * @var string PayPal expiration date for Access Token
     */
    public $paypal_token_expire;

    /**
     * @var string PayPal expiration date for Authorization
     */
    public $paypal_authorization_expire;

    /**
     * @var bool
     */
    public $isExpressCheckout = false;

    /**
     * @var bool
     */
    public $isHostedFields = false;

    /**
     * @var string Creation date in mysql date format
     */
    public $date_add;

    /**
     * @var string Last modification date in mysql date format
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
                'size' => 20,
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
}
