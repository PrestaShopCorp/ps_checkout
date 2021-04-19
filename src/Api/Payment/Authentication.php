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

namespace PrestaShop\Module\PrestashopCheckout\Api\Payment;

use PrestaShop\Module\PrestashopCheckout\Api\Payment\Client\PaymentClient;
use PrestaShop\Module\PrestashopCheckout\Exception\PsCheckoutSessionException;

/**
 * Handle dispute calls
 */
class Authentication extends PaymentClient
{
    /**
     * Get an auth token from PSL
     *
     * @param string $type
     * @param string $correlationId
     *
     * @return array
     */
    public function getAuthToken($type, $correlationId)
    {
        $this->setRoute('/' . $type . '-sessions');

        $response = $this->post([
            'headers' => [
                'X-Correlation-Id' => $correlationId,
            ],
        ]);

        if (!$response['status']) {
            $this->module->getLogger()->error(
                'Unable to retrieve token from PSL',
                [
                    'response' => $response,
                ]
            );

            throw new PsCheckoutSessionException('Unable to retrieve ' . $type . ' authentication token from PSL', PsCheckoutSessionException::UNABLE_TO_RETRIEVE_TOKEN);
        }

        // Set token expiration date to server timezone
        $authToken = $response['body'];
        $timezone = date_timezone_get(date_create(date('Y-m-d H:i:s')));
        $authToken['expires_at'] = date_format(date_timezone_set(date_create($authToken['expires_at']), $timezone), 'Y-m-d H:i:s');

        return $authToken;
    }
}
