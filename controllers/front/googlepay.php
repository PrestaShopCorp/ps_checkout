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

use PrestaShop\Module\PrestashopCheckout\Cart\ValueObject\CartId;
use PrestaShop\Module\PrestashopCheckout\CommandBus\CommandBusInterface;
use PrestaShop\Module\PrestashopCheckout\Controller\AbstractFrontController;
use PrestaShop\Module\PrestashopCheckout\PayPal\GooglePay\Query\GetGooglePayTransactionInfoQuery;

/**
 * This controller receive ajax call on customer click on a payment button
 */
class Ps_CheckoutGooglepayModuleFrontController extends AbstractFrontController
{
    /**
     * @var Ps_checkout
     */
    public $module;

    /**
     * @var CommandBusInterface
     */
    private $commandBus;

    /**
     * @see FrontController::postProcess()
     */
    public function postProcess()
    {
        try {
            $bodyValues = [];
            $bodyContent = file_get_contents('php://input');

            if (!empty($bodyContent)) {
                $bodyValues = json_decode($bodyContent, true);
            }

            $action = $bodyValues['action'];

            $this->commandBus = $this->module->getService('ps_checkout.bus.command');

            if ($action === 'getTransactionInfo') {
                $this->getTransactionInfo($bodyValues);
            } else {
                $this->exitWithExceptionMessage(new Exception('Invalid request', 400));
            }
        } catch (Exception $exception) {
            $this->exitWithExceptionMessage($exception);
        }
    }

    private function getTransactionInfo(array $bodyValues)
    {
        $transactionInfo = $this->commandBus->handle(new GetGooglePayTransactionInfoQuery(new CartId($this->context->cart->id)));

        $this->exitWithResponse([
            'httpCode' => 200,
            'body' => $transactionInfo->getPayload()->toArray(),
        ]);
    }
}
