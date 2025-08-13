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

use PsCheckout\Core\Webhook\WebhookException;
use PsCheckout\Core\WebhookDispatcher\Action\CheckPSLSignatureAction;
use PsCheckout\Core\WebhookDispatcher\Processor\DispatchWebhookProcessor;
use PsCheckout\Core\WebhookDispatcher\Validator\BodyValuesValidator;
use PsCheckout\Core\WebhookDispatcher\Validator\HeaderValuesValidator;
use PsCheckout\Core\WebhookDispatcher\Validator\WebhookShopIdValidator;
use PsCheckout\Core\WebhookDispatcher\ValueObject\DispatchWebhookRequest;
use PsCheckout\Infrastructure\Controller\AbstractFrontController;
use Psr\Log\LoggerInterface;

class ps_checkoutDispatchWebHookModuleFrontController extends AbstractFrontController
{
    const PS_CHECKOUT_PAYPAL_ID_LABEL = 'PS_CHECKOUT_PAYPAL_ID_MERCHANT';

    /**
     * @var bool If set to true, will be redirected to authentication page
     */
    public $auth = false;

    /**
     * @return bool
     *
     * @throws \PsCheckout\Core\Exception\PsCheckoutException
     */
    public function display(): bool
    {
        /** @var LoggerInterface $logger */
        $logger = $this->module->getService(LoggerInterface::class);

        $logger->info('Webhook dispatch initiated');

        try {
            /** @var HeaderValuesValidator $headerValuesValidator */
            $headerValuesValidator = $this->module->getService(HeaderValuesValidator::class);
            $headerValues = $headerValuesValidator->validate();
            $logger->info('Headers validated', $headerValues);

            /** @var BodyValuesValidator $bodyValuesValidator */
            $bodyValuesValidator = $this->module->getService(BodyValuesValidator::class);
            $bodyValues = $bodyValuesValidator->validate();
            $logger->info('Body validated', $bodyValues);

            $dispatchWebhookRequest = DispatchWebhookRequest::createFromRequest($bodyValues, $headerValues);

            /** @var CheckPSLSignatureAction $checkPSLSignatureAction */
            $checkPSLSignatureAction = $this->module->getService(CheckPSLSignatureAction::class);
            $checkPSLSignatureAction->execute($bodyValues);
            $logger->info('PSLS Signature validated', $bodyValues);

            /** @var WebhookShopIdValidator $webhookShopIdValidator */
            $webhookShopIdValidator = $this->module->getService(WebhookShopIdValidator::class);
            $webhookShopIdValidator->validate($dispatchWebhookRequest->getShopId());

            $logger->info('Webhook dispatch started');

            /** @var DispatchWebhookProcessor $dispatchWebHookProcessor */
            $dispatchWebHookProcessor = $this->module->getService(DispatchWebhookProcessor::class);

            return $dispatchWebHookProcessor->process($dispatchWebhookRequest);
        } catch (WebhookException $e) {
            // Handle the exception
            $logger->error('Webhook Dispatcher error: ' . $e->getMessage());
            http_response_code($e->getCode());

            echo json_encode(['error' => $e->getMessage()]);
        }

        return false;
    }

    /**
     * Override displayMaintenancePage to prevent the maintenance page to be displayed
     *
     * @see FrontController::displayMaintenancePage()
     */
    protected function displayMaintenancePage()
    {
        return;
    }

    /**
     * Override displayRestrictedCountryPage to prevent page country is not allowed
     *
     * @see FrontController::displayRestrictedCountryPage()
     */
    protected function displayRestrictedCountryPage()
    {
        return;
    }

    /**
     * Override geolocationManagement to prevent country GEOIP blocking
     *
     * @see FrontController::geolocationManagement()
     *
     * @param Country $defaultCountry
     *
     * @return false
     */
    protected function geolocationManagement($defaultCountry)
    {
        return false;
    }

    /**
     * Override sslRedirection to prevent redirection
     *
     * @see FrontController::sslRedirection()
     */
    protected function sslRedirection()
    {
        return;
    }

    /**
     * Override canonicalRedirection to prevent redirection
     *
     * @see FrontController::canonicalRedirection()
     *
     * @param string $canonical_url
     */
    protected function canonicalRedirection($canonical_url = '')
    {
        return;
    }
}
