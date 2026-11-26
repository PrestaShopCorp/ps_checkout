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

namespace PsCheckout\Core\PayPal\Order\Action;

use Exception;
use PsCheckout\Core\Exception\PsCheckoutException;
use PsCheckout\Core\Order\Action\CreateOrderActionInterface;
use PsCheckout\Core\PayPal\Order\Exception\CartAlreadyPaidException;
use PsCheckout\Core\PayPal\Order\Validator\PaidCartValidatorInterface;
use Psr\Log\LoggerInterface;

class RefusePaidCartAction implements RefusePaidCartActionInterface
{
    /**
     * @var PaidCartValidatorInterface
     */
    private $paidCartValidator;

    /**
     * @var CreateOrderActionInterface
     */
    private $createOrderAction;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        PaidCartValidatorInterface $paidCartValidator,
        CreateOrderActionInterface $createOrderAction,
        LoggerInterface $logger
    ) {
        $this->paidCartValidator = $paidCartValidator;
        $this->createOrderAction = $createOrderAction;
        $this->logger = $logger;
    }

    /**
     * {@inheritDoc}
     */
    public function execute(int $cartId)
    {
        try {
            $this->paidCartValidator->validate($cartId);
        } catch (CartAlreadyPaidException $exception) {
            $paidPayPalOrder = $exception->getPaidPayPalOrder();

            // The customer paid but has no order yet: create it now instead of waiting for the webhook.
            if ($paidPayPalOrder) {
                try {
                    $this->createOrderAction->execute($paidPayPalOrder);
                } catch (Exception $creationException) {
                    if ($creationException->getCode() !== PsCheckoutException::PRESTASHOP_ORDER_ALREADY_EXISTS) {
                        $this->logger->error(
                            'Order could not be created for an already paid PayPal order',
                            ['cart_id' => $cartId, 'paypal_order_id' => $paidPayPalOrder->getId(), 'exception' => $creationException]
                        );
                    }
                }
            }

            throw $exception;
        }
    }
}
