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
use PrestaShop\Module\PrestashopCheckout\PaymentMethodToken\Query\GetCustomerPaymentMethodTokensQuery;
use PrestaShop\Module\PrestashopCheckout\PaymentMethodToken\Query\GetCustomerPaymentMethodTokensQueryResult;
use Psr\Log\LoggerInterface;

/**
 * This controller receive ajax call to manage the Customer PayPal Payment Method tokens
 */
class Ps_CheckoutVaultModuleFrontController extends AbstractFrontController
{
    /**
     * @see FrontController::postProcess()
     */
    public function postProcess()
    {
        try {
            /** @var CommandBusInterface $commandBus */
            $commandBus = $this->module->getService('ps_checkout.bus.command');
            /** @var GetCustomerPaymentMethodTokensQueryResult $getCustomerPaymentMethodTokensQueryResult */
            $getCustomerPaymentMethodTokensQueryResult = $commandBus->handle(new GetCustomerPaymentMethodTokensQuery(
                $this->getCustomerId(),
                $this->getPageSize(),
                $this->getPageNumber()
            ));

            $this->exitWithResponse([
                'status' => true,
                'httpCode' => 200,
                'body' => [
                    'customerId' => $getCustomerPaymentMethodTokensQueryResult->getCustomerId(),
                    'paymentTokens' => $getCustomerPaymentMethodTokensQueryResult->getPaymentTokens(),
                    'totalItems' => $getCustomerPaymentMethodTokensQueryResult->getTotalItems(),
                    'totalPages' => $getCustomerPaymentMethodTokensQueryResult->getTotalPages(),
                ],
            ]);
        } catch (Exception $exception) {
            /** @var LoggerInterface $logger */
            $logger = $this->module->getService('ps_checkout.logger');
            $logger->error(
                sprintf(
                    'VaultController exception %s : %s',
                    $exception->getCode(),
                    $exception->getMessage()
                )
            );

            $this->exitWithExceptionMessage($exception);
        }
    }
}
