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

namespace PsCheckout\Core\PayPal\Order\Validator;

use PsCheckout\Core\Order\Validator\CartOwnershipValidatorInterface;
use PsCheckout\Core\PayPal\Order\Repository\PayPalOrderRepositoryInterface;
use Psr\Log\LoggerInterface;

class PayPalOrderOwnershipValidator implements PayPalOrderOwnershipValidatorInterface
{
    /**
     * @var PayPalOrderRepositoryInterface
     */
    private $payPalOrderRepository;

    /**
     * @var CartOwnershipValidatorInterface
     */
    private $cartOwnershipValidator;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        PayPalOrderRepositoryInterface $payPalOrderRepository,
        CartOwnershipValidatorInterface $cartOwnershipValidator,
        LoggerInterface $logger
    ) {
        $this->payPalOrderRepository = $payPalOrderRepository;
        $this->cartOwnershipValidator = $cartOwnershipValidator;
        $this->logger = $logger;
    }

    /**
     * {@inheritDoc}
     */
    public function isOwnedByCurrentVisitor(string $payPalOrderId): bool
    {
        $payPalOrder = $this->payPalOrderRepository->getOneBy(['id' => $payPalOrderId]);

        if (!$payPalOrder) {
            $this->logger->warning('PayPal Order not found', ['PayPalOrderId' => $payPalOrderId]);

            return false;
        }

        if ($this->cartOwnershipValidator->isOwnedByCurrentVisitor($payPalOrder->getIdCart())) {
            return true;
        }

        $this->logger->warning(
            'PayPal Order does not belong to the current visitor',
            [
                'PayPalOrderId' => $payPalOrderId,
                'PayPalOrderCartId' => $payPalOrder->getIdCart(),
            ]
        );

        return false;
    }
}
