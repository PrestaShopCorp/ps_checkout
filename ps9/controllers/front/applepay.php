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

use PsCheckout\Core\Exception\PsCheckoutException;
use PsCheckout\Core\PayPal\ApplePay\Builder\ApplePayPaymentRequestDataBuilder;
use PsCheckout\Core\Settings\Configuration\PayPalConfiguration;
use PsCheckout\Infrastructure\Adapter\Configuration;
use PsCheckout\Infrastructure\Controller\AbstractFrontController;
use PsCheckout\Utility\Common\InputStreamUtility;

/**
 * This controller receives AJAX calls when a customer clicks on a payment button.
 */
class Ps_CheckoutApplepayModuleFrontController extends AbstractFrontController
{
    /**
     * @var Ps_checkout
     */
    public $module;

    /**
     * @see FrontController::postProcess()
     */
    public function postProcess()
    {
        try {
            $action = $this->getActionFromRequest();

            if (!$action) {
                throw new PsCheckoutException('Invalid request', 400);
            }

            switch ($action) {
                case 'getPaymentRequest':
                    $this->getPaymentRequest();

                    break;
                case 'getDomainAssociation':
                    $this->handleDomainAssociation();

                    break;
                default:
                    throw new Exception('Invalid request', 400);
            }
        } catch (Exception $exception) {
            $this->exitWithExceptionMessage($exception);
        }
    }

    /**
     * Extracts action from the request body or GET parameters.
     *
     * @return string|null
     */
    private function getActionFromRequest()
    {
        /** @var InputStreamUtility $inputStreamUtility */
        $inputStreamUtility = $this->module->getService(InputStreamUtility::class);
        $bodyContent = $inputStreamUtility->getBodyContent();

        if (!empty($bodyContent)) {
            $bodyValues = json_decode($bodyContent, true);
            if (!empty($bodyValues['action'])) {
                return $bodyValues['action'];
            }
        }

        $action = Tools::getValue('action');

        return $action === 'getDomainAssociation' ? $action : null;
    }

    /**
     * Handles the domain association file retrieval for Apple Pay validation.
     *
     * @return void
     */
    private function handleDomainAssociation()
    {
        /** @var Configuration $configuration */
        $configuration = $this->module->getService(Configuration::class);

        $environment = $configuration->get(PayPalConfiguration::PS_CHECKOUT_PAYMENT_MODE);

        $associationFile = _PS_MODULE_DIR_ . $this->module->name . "/.well-known/apple-$environment-merchantid-domain-association";

        if (!file_exists($associationFile)) {
            throw new Exception('File not found', 404);
        }

        if (!headers_sent()) {
            ob_end_clean();
            header('Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
            header('X-Robots-Tag: noindex, nofollow');
            header_remove('Last-Modified');
            header('Content-Type: text/plain', true, 200);
        }

        echo file_get_contents($associationFile);

        exit;
    }

    /**
     * Handles the payment request for Apple Pay.
     *
     * @return void
     */
    private function getPaymentRequest()
    {
        /** @var ApplePayPaymentRequestDataBuilder $applePayPaymentRequestDataBuilder */
        $applePayPaymentRequestDataBuilder = $this->module->getService(ApplePayPaymentRequestDataBuilder::class);
        $paymentRequest = $applePayPaymentRequestDataBuilder->build();

        $this->exitWithResponse([
            'httpCode' => 200,
            'body' => $paymentRequest->toArray(),
        ]);
    }
}
