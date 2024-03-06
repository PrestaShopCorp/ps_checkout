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
use PrestaShop\Module\PrestashopCheckout\Exception\PsCheckoutException;
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

        try {
            /** @var WebhookHandler $webhookHandler */
            $webhookHandler = $this->module->getService('ps_checkout.webhook.handler');

            if (empty($_SERVER['HTTP_WEBHOOK_SECRET']) || !$webhookHandler->authenticate($_SERVER['HTTP_WEBHOOK_SECRET'])) {
                throw new WebhookException('Webhook secret mismatch', WebhookException::WEBHOOK_SECRET_MISMATCH);
            }

            $payload = $this->getPayload();
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
                    'resource' => $payload['resource'],
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
                        'httpCode' => 401,
                        'error' => $exception->getMessage(),
                    ]);
                    break;
                default:
                    $this->exitWithResponse([
                        'httpCode' => 400,
                        'error' => $exception->getMessage(),
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
     * @return array{id: string, createTime: string, eventType: string, eventVersion: string, summary: string, resourceType: string, resource: array}
     *
     * @throws PsCheckoutException
     */
    private function getPayload()
    {
        $content = file_get_contents('php://input');

        if (empty($content)) {
            throw new WebhookException('Webhook payload is missing.', WebhookException::WEBHOOK_PAYLOAD_INVALID);
        }

        $payload = json_decode($content, true);

        if (null === $payload && JSON_ERROR_NONE !== json_last_error()) {
            throw new PsCheckoutException('Webhook payload cannot be decoded: ' . json_last_error_msg(), WebhookException::WEBHOOK_PAYLOAD_INVALID);
        }

        if (empty($payload['id'])) {
            throw new WebhookException('Webhook id is missing', WebhookException::WEBHOOK_PAYLOAD_EVENT_TYPE_MISSING);
        }

        if (empty($payload['createTime'])) {
            throw new WebhookException('Webhook createTime is missing', WebhookException::WEBHOOK_PAYLOAD_EVENT_TYPE_MISSING);
        }

        if (empty($payload['eventType'])) {
            throw new WebhookException('Webhook eventType is missing', WebhookException::WEBHOOK_PAYLOAD_EVENT_TYPE_MISSING);
        }

        if (empty($payload['eventVersion'])) {
            throw new WebhookException('Webhook eventVersion is missing', WebhookException::WEBHOOK_PAYLOAD_EVENT_TYPE_MISSING);
        }

        if (empty($payload['summary'])) {
            throw new WebhookException('Webhook summary is missing', WebhookException::WEBHOOK_PAYLOAD_EVENT_TYPE_MISSING);
        }

        if (empty($payload['resourceType'])) {
            throw new WebhookException('Webhook resourceType is missing', WebhookException::WEBHOOK_PAYLOAD_EVENT_TYPE_MISSING);
        }

        if (empty($payload['resource'])) {
            throw new WebhookException('Webhook resource is missing', WebhookException::WEBHOOK_PAYLOAD_RESOURCE_MISSING);
        }

        return $payload;
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
