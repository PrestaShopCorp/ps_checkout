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

use PrestaShop\Module\PrestashopCheckout\Controller\AbstractFrontController;
use PrestaShop\Module\PrestashopCheckout\Webhook\WebhookException;
use PrestaShop\Module\PrestashopCheckout\Webhook\WebhookHandler;
use Psr\Log\LoggerInterface;

/**
 * This controller receive webhook from API to performs asynchronous changes
 */
class Ps_CheckoutWebhookModuleFrontController extends AbstractFrontController
{
    /**
     * @var Ps_checkout
     */
    public $module;

    /**
     * @var bool If set to true, will be redirected to authentication page
     */
    public $auth = false;

    /**
     * @see FrontController::postProcess()
     */
    public function postProcess()
    {
        /** @var LoggerInterface $logger */
        $logger = $this->module->getService('ps_checkout.logger');
        /** @var \PrestaShop\Module\PrestashopCheckout\Webhook\WebhookHelper $webhookHelper */
        $webhookHelper = $this->module->getService('ps_checkout.webhook.helper');

        try {
            /** @var WebhookHandler $webhookHandler */
            $webhookHandler = $this->module->getService('ps_checkout.webhook.handler');

            if (empty($_SERVER['HTTP_WEBHOOK_SECRET']) || !$webhookHandler->authenticate($_SERVER['HTTP_WEBHOOK_SECRET'])) {
                throw new WebhookException('Webhook secret mismatch', WebhookException::WEBHOOK_SECRET_MISMATCH);
            }

            $payload = $webhookHelper->getPayload(file_get_contents('php://input'));
            $webhookHandler->handle($payload);

            $logger->debug(
                'Webhook handled successfully',
                [
                    'id' => $payload['id'],
                    'createTime' => $payload['createTime'],
                    'eventType' => $payload['eventType'],
                    'eventVersion' => $payload['eventVersion'],
                    'summary' => $payload['summary'],
                    'resourceType' => $payload['resourceType'],
                ]
            );
            $this->exitWithResponse([
                'httpCode' => 200,
            ]);
            exit;
        } catch (WebhookException $exception) {
            switch ($exception->getCode()) {
                case WebhookException::WEBHOOK_SECRET_MISMATCH:
                    $this->exitWithResponse([
                        'httpCode' => 403,
                    ]);
                    break;
                default:
                    $this->exitWithResponse([
                        'httpCode' => 400,
                    ]);
            }
            exit;
        } catch (Exception $exception) {
            $logger->error(
                'Webhook cannot be handled',
                [
                    'exception' => $exception,
                ]
            );
            $this->exitWithResponse([
                'httpCode' => 500,
            ]);
            exit;
        }
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
