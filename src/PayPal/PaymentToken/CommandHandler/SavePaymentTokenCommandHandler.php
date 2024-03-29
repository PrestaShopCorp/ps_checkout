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
use PrestaShop\Module\PrestashopCheckout\PayPal\PaymentToken\Command\SavePaymentTokenCommand;
use PrestaShop\Module\PrestashopCheckout\PayPal\PaymentToken\Entity\PaymentToken;
use PrestaShop\Module\PrestashopCheckout\Repository\PaymentTokenRepository;

class SavePaymentTokenCommandHandler
{
    /** @var PaymentTokenRepository */
    private $paymentTokenRepository;

    public function __construct(PaymentTokenRepository $paymentTokenRepository)
    {
        $this->paymentTokenRepository = $paymentTokenRepository;
    }

    /**
     * @throws Exception
     */
    public function handle(SavePaymentTokenCommand $command)
    {
        $token = new PaymentToken(
            $command->getPaymentTokenId()->getValue(),
            $command->getPaypalCustomerId()->getValue(),
            $command->getPaymentSource(),
            $command->getPaymentTokenData(),
            $command->getMerchantId(),
            $command->getStatus(),
            $command->isFavorite()
        );
        $this->paymentTokenRepository->save($token);

        if ($command->isFavorite()) {
            $this->paymentTokenRepository->setTokenFavorite($command->getPaymentTokenId());
        }
    }
}
