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
if (!defined('_PS_VERSION_')) {
    exit;
}

use PsCheckout\Core\PayPal\ShippingCallback\Action\VerifyShippingCallbackSignatureActionInterface;
use PsCheckout\Core\PayPal\ShippingCallback\Exception\ShippingCallbackException;
use PsCheckout\Core\PayPal\ShippingCallback\Provider\CallbackHeaderProviderInterface;
use PsCheckout\Core\PayPal\ShippingCallback\Service\ShippingCallbackProcessorInterface;
use PsCheckout\Core\PayPal\ShippingCallback\ValueObject\ShippingCallbackPayload;
use PsCheckout\Infrastructure\Controller\AbstractFrontController;
use PsCheckout\Utility\Common\InputStreamUtility;
use Psr\Log\LoggerInterface;

class Ps_CheckoutShippingModuleFrontController extends AbstractFrontController
{
    /**
     * @var bool If set to true, will be redirected to authentication page
     */
    public $auth = false;

    /**
     * @see FrontController::postProcess()
     */
    public function postProcess()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->exitWithServerResponse(405, 'Method Not Allowed');
        }

        /** @var LoggerInterface $logger */
        $logger = $this->module->getService(LoggerInterface::class);

        try {
            $idCart = (int) Tools::getValue('id_cart');

            if (!$idCart) {
                $this->exitWithServerResponse(400, 'Missing id_cart parameter');
            }

            /** @var InputStreamUtility $inputStreamUtility */
            $inputStreamUtility = $this->module->getService(InputStreamUtility::class);
            $bodyContent = $inputStreamUtility->getBodyContent();

            if (empty($bodyContent)) {
                $this->exitWithServerResponse(400, 'Empty payload');
            }

            $data = json_decode($bodyContent, true);

            if (!is_array($data)) {
                $this->exitWithServerResponse(400, 'Invalid JSON payload');
            }

            /** @var CallbackHeaderProviderInterface $headerProvider */
            $headerProvider = $this->module->getService(CallbackHeaderProviderInterface::class);
            $callbackHeaders = $headerProvider->getHeaders();

            /** @var VerifyShippingCallbackSignatureActionInterface $signatureVerifier */
            $signatureVerifier = $this->module->getService(VerifyShippingCallbackSignatureActionInterface::class);
            $signatureVerifier->execute($bodyContent, $callbackHeaders);

            $payload = new ShippingCallbackPayload($data);

            /** @var ShippingCallbackProcessorInterface $processor */
            $processor = $this->module->getService(ShippingCallbackProcessorInterface::class);
            $response = $processor->process($idCart, $payload);

            $logger->info('ShippingController - Callback processed', [
                'id_cart' => $idCart,
                'paypal_headers' => $callbackHeaders,
                'payload' => $data,
                'response' => $response,
            ]);

            $this->exitWithServerResponse(200, $response);
        } catch (ShippingCallbackException $exception) {
            $logger->warning(
                'ShippingController - Callback declined: ' . $exception->getMessage(),
                [
                    'issue' => $exception->getIssue(),
                    'id_cart' => isset($idCart) ? $idCart : null,
                    'paypal_headers' => isset($callbackHeaders) ? $callbackHeaders : null,
                ]
            );

            if ($exception->getIssue() === ShippingCallbackException::INVALID_SIGNATURE) {
                $this->exitWithServerResponse(401, [
                    'name' => 'UNAUTHORIZED',
                    'details' => [
                        ['issue' => $exception->getIssue()],
                    ],
                ]);
            }

            $this->exitWithServerResponse(422, [
                'name' => 'UNPROCESSABLE_ENTITY',
                'details' => [
                    ['issue' => $exception->getIssue()],
                ],
            ]);
        } catch (Throwable $exception) {
            $logger->error('ShippingController - Unexpected error: ' . $exception->getMessage(), [
                'exception' => $exception,
                'id_cart' => isset($idCart) ? $idCart : null,
            ]);
            $this->exitWithServerResponse(500, ['name' => 'INTERNAL_SERVER_ERROR']);
        }
    }
}
