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

use PrestaShop\Module\PrestashopCheckout\CommandBus\CommandBusInterface;
use PrestaShop\Module\PrestashopCheckout\Controller\AbstractFrontController;
use PrestaShop\Module\PrestashopCheckout\PayPal\OAuth\Query\GetPayPalGetUserIdTokenQuery;
use PrestaShop\Module\PrestashopCheckout\PayPal\OAuth\Query\GetPayPalGetUserIdTokenQueryResult;
use Psr\Log\LoggerInterface;

/**
 * This controller receive ajax call to retrieve a PayPal User Id Token
 */
class Ps_CheckoutTokenModuleFrontController extends AbstractFrontController
{
    /**
     * @see FrontController::postProcess()
     */
    public function postProcess()
    {
        try {
            /** @var CommandBusInterface $commandBus */
            $commandBus = $this->module->getService('ps_checkout.bus.command');
            /** @var GetPayPalGetUserIdTokenQueryResult $getPayPalGetUserIdTokenQueryResult */
            $getPayPalGetUserIdTokenQueryResult = $commandBus->handle(new GetPayPalGetUserIdTokenQuery($this->getCustomerId()));

            $this->exitWithResponse([
                'status' => true,
                'httpCode' => 200,
                'body' => [
                    'token' => $getPayPalGetUserIdTokenQueryResult->getUserIdToken(),
                ],
            ]);
        } catch (Exception $exception) {
            /** @var LoggerInterface $logger */
            $logger = $this->module->getService('ps_checkout.logger');
            $logger->error(
                sprintf(
                    'TokenController exception %s : %s',
                    $exception->getCode(),
                    $exception->getMessage()
                )
            );

            $this->exitWithExceptionMessage($exception);
        }
    }
}
