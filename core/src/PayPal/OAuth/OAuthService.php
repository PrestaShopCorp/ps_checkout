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

namespace PsCheckout\Core\PayPal\OAuth;

use Exception;
use PsCheckout\Api\Http\CheckoutHttpClientInterface;

class OAuthService implements OAuthServiceInterface
{
    private $httpClient;

    public function __construct(CheckoutHttpClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    /**
     * {@inheritdoc}
     */
    public function getUserIdToken(string $merchantId, $payPalCustomerId = null): string
    {
        try {
            $response = $this->httpClient->getUserIdToken($merchantId, $payPalCustomerId);

            $data = json_decode($response->getBody(), true);

            if (empty($data['id_token'])) {
                throw new Exception('Failed to get PayPal User ID token from response.');
            }

            return $data['id_token'];
        } catch (Exception $exception) {
            throw new Exception('Failed to get PayPal User ID token.', 0, $exception);
        }
    }
}
