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

use PrestaShop\Module\PrestashopCheckout\Cart\Exception\CartException;
use PrestaShop\Module\PrestashopCheckout\Cart\ValueObject\CartId;
use PrestaShop\Module\PrestashopCheckout\CommandBus\CommandBusInterface;
use PrestaShop\Module\PrestashopCheckout\Controller\AbstractFrontController;
use PrestaShop\Module\PrestashopCheckout\PayPal\ApplePay\Query\GetApplePayPaymentRequestQuery;
use PrestaShop\Module\PrestashopCheckout\PayPal\PayPalConfiguration;

/**
 * This controller receive ajax call on customer click on a payment button
 */
class Ps_CheckoutApplepayModuleFrontController extends AbstractFrontController
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
            $action = '';
            $bodyContent = file_get_contents('php://input');

            if (!empty($bodyContent)) {
                $bodyValues = json_decode($bodyContent, true);
                $action = $bodyValues['action'];
            }

            if (empty($action)) {
                $getParam = Tools::getValue('action');
                if ($getParam === 'getDomainAssociation') {
                    $action = $getParam;
                }
            }

            $this->commandBus = $this->module->getService('ps_checkout.bus.command');

            switch ($action) {
                case 'getPaymentRequest':
                    $this->getPaymentRequest();
                    break;
                case 'getDomainAssociation':
                    /**
                     * @var PayPalConfiguration $payPalConfiguration
                     */
                    $payPalConfiguration = $this->module->getService(PayPalConfiguration::class);
                    $environment = $payPalConfiguration->getPaymentMode();
                    $associationFile = _PS_MODULE_DIR_ . "ps_checkout/.well-known/apple-$environment-merchantid-domain-association";
                    if (file_exists($associationFile)) {
                        if (!headers_sent()) {
                            ob_end_clean();
                            header('Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
                            header('X-Robots-Tag: noindex, nofollow');
                            header_remove('Last-Modified');
                            header('Content-Type: text/plain', true, 200);
                        }
                        echo file_get_contents($associationFile);
                        exit;
                    } else {
                        $this->exitWithExceptionMessage(new Exception('File not found', 404));
                    }
                    break;
                default:
                    $this->exitWithExceptionMessage(new Exception('Invalid request', 400));
            }
        } catch (Exception $exception) {
            $this->exitWithExceptionMessage($exception);
        }
    }

    /**
     * @return void
     *
     * @throws CartException
     */
    private function getPaymentRequest()
    {
        $cartId = new CartId($this->context->cart->id);
        $query = new GetApplePayPaymentRequestQuery($cartId);
        $paymentRequest = $this->commandBus->handle($query);

        $this->exitWithResponse([
            'httpCode' => 200,
            'body' => $paymentRequest->getPayload()->toArray(),
        ]);
    }
}
