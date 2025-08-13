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

namespace PsCheckout\Core\PayPal\Order\ValueObject;

/**
 * Class PayPalOrderCompletionData
 * Represents PayPal order data required for order completion.
 */
class PayPalOrderCompletionData
{
    /** @var string */
    private $paypalStatus;

    /** @var string */
    private $paypalOrderId;

    /** @var string */
    private $paypalTransactionId;

    /** @var int */
    private $idCart;

    /** @var int */
    private $idModule;

    /** @var int */
    private $idOrder;

    /** @var string */
    private $secureKey;

    /**
     * PayPalOrderCompletionData constructor.
     *
     * @param string $paypalStatus
     * @param string $paypalOrderId
     * @param string $paypalTransactionId
     * @param int $idCart
     * @param int $idModule
     * @param int $idOrder
     * @param string $secureKey
     */
    public function __construct(
        string $paypalStatus,
        string $paypalOrderId,
        string $paypalTransactionId,
        int $idCart,
        int $idModule,
        int $idOrder,
        string $secureKey
    ) {
        $this->paypalStatus = $paypalStatus;
        $this->paypalOrderId = $paypalOrderId;
        $this->paypalTransactionId = $paypalTransactionId;
        $this->idCart = (int) $idCart;
        $this->idModule = (int) $idModule;
        $this->idOrder = (int) $idOrder;
        $this->secureKey = $secureKey;
    }

    /**
     * Convert object data to an array
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'paypal_status' => $this->paypalStatus,
            'paypal_order' => $this->paypalOrderId,
            'paypal_transaction' => $this->paypalTransactionId,
            'id_cart' => $this->idCart,
            'id_module' => $this->idModule,
            'id_order' => $this->idOrder,
            'secure_key' => $this->secureKey,
        ];
    }
}
