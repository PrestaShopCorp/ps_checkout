<?php
/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License 3.0 (AFL-3.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 */

use PrestaShop\Module\PrestashopCheckout\Exception\PsCheckoutException;
use PrestaShop\Module\PrestashopCheckout\Handler\ExceptionHandler;

/**
 * This controller receive ajax call to retrieve a PayPal Client Token
 */
class Ps_CheckoutTokenModuleFrontController extends ModuleFrontController
{
    /**
     * @var Ps_checkout
     */
    public $module;

    /**
     * @var ExceptionHandler
     */
    private $exceptionHandler;


    public function __construct()
    {
        parent::__construct();

        $this->exceptionHandler = $this->module->getService('ps_checkout.handler.exception');
    }

    /**
     * @see FrontController::postProcess()
     *
     * @todo Move logic to a Service and refactor
     */
    public function postProcess()
    {
        header('content-type:application/json');

        try {
            if (false === Validate::isLoadedObject($this->context->cart)) {
                throw new PsCheckoutException('No cart found.', PsCheckoutException::PRESTASHOP_CONTEXT_INVALID);
            }

            /** @var \PrestaShop\Module\PrestashopCheckout\Repository\PsCheckoutCartRepository $psCheckoutCartRepository */
            $psCheckoutCartRepository = $this->module->getService('ps_checkout.repository.pscheckoutcart');

            /** @var PsCheckoutCart|false $psCheckoutCart */
            $psCheckoutCart = $psCheckoutCartRepository->findOneByCartId((int) $this->context->cart->id);

            if (false === $psCheckoutCart) {
                $psCheckoutCart = new PsCheckoutCart();
                $psCheckoutCart->id_cart = (int) $this->context->cart->id;
            }

            // If paypal_token_expire is in future, token is not expired
            if (empty($psCheckoutCart->paypal_token_expire)
                || strtotime($psCheckoutCart->paypal_token_expire) <= time()
            ) {
                $psCheckoutCart->paypal_order = '';
                $psCheckoutCart->paypal_token = $this->getToken();
                $psCheckoutCart->paypal_token_expire = (new DateTime())->modify('+3550 seconds')->format('Y-m-d H:i:s');
                $psCheckoutCartRepository->save($psCheckoutCart);
            }

            echo json_encode([
                'status' => true,
                'httpCode' => 200,
                'body' => [
                    'token' => $psCheckoutCart->paypal_token,
                ],
                'exceptionCode' => null,
                'exceptionMessage' => null,
            ]);
        } catch (Exception $exception) {
            $this->exceptionHandler->handle($exception, false);

            /* @var \Psr\Log\LoggerInterface logger */
            $logger = $this->module->getService('ps_checkout.logger');
            $logger->error(
                sprintf(
                    'TokenController exception %s : %s',
                    $exception->getCode(),
                    $exception->getMessage()
                )
            );

            header('HTTP/1.0 500 Internal Server Error');

            echo json_encode([
                'status' => false,
                'httpCode' => 500,
                'body' => '',
                'exceptionCode' => $exception->getCode(),
                'exceptionMessage' => $exception->getMessage(),
            ]);
        }

        exit;
    }

    /**
     * @return string
     */
    private function getToken()
    {
        /** @var \PrestaShop\Module\PrestashopCheckout\Repository\PaypalAccountRepository $paypalAccountRepository */
        $paypalAccountRepository = $this->module->getService('ps_checkout.repository.paypal.account');

        $apiOrder = new PrestaShop\Module\PrestashopCheckout\Api\Payment\Order(\Context::getContext()->link);
        $response = $apiOrder->generateClientToken($paypalAccountRepository->getMerchantId());

        if (empty($response['body']) || empty($response['body']['client_token'])) {
            throw new Exception('Unable to retrieve PayPal Client Token');
        }

        return $response['body']['client_token'];
    }
}
