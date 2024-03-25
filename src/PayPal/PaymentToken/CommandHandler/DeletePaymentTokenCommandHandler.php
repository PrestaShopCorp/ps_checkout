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

namespace PrestaShop\Module\PrestashopCheckout\PayPal\PaymentToken\CommandHandler;

use Exception;
use PrestaShop\Module\PrestashopCheckout\PayPal\PaymentToken\Command\DeletePaymentTokenCommand;
use PrestaShop\Module\PrestashopCheckout\PayPal\PaymentToken\PaymentMethodTokenService;
use PrestaShop\Module\PrestashopCheckout\Repository\PaymentTokenRepository;

class DeletePaymentTokenCommandHandler
{
    /** @var PaymentTokenRepository */
    private $paymentTokenRepository;
    /**
     * @var PaymentMethodTokenService
     */
    private $paymentMethodTokenService;

    public function __construct(PaymentMethodTokenService $paymentMethodTokenService, PaymentTokenRepository $paymentTokenRepository)
    {
        $this->paymentTokenRepository = $paymentTokenRepository;
        $this->paymentMethodTokenService = $paymentMethodTokenService;
    }

    /**
     * @throws Exception
     */
    public function handle(DeletePaymentTokenCommand $command)
    {
        $tokenBelongsToCustomer = false;
        $tokens = $this->paymentTokenRepository->findByPrestaShopCustomerId($command->getCustomerId()->getValue());

        foreach ($tokens as $token) {
            $tokenBelongsToCustomer |= $token->getId()->getValue() === $command->getPaymentTokenId()->getValue();
        }

        if ($tokenBelongsToCustomer) {
            $this->paymentMethodTokenService->deletePaymentToken($command->getPaymentTokenId());
            $this->paymentTokenRepository->deleteById($command->getPaymentTokenId());
        } else {
            throw new Exception('Failed to remove saved payment token');
        }
    }
}
