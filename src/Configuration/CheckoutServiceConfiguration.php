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
namespace PrestaShop\Module\PrestashopCheckout\Configuration;

use PrestaShop\Module\PrestashopCheckout\Api\Payment\Order;
use PrestaShop\Module\PrestashopCheckout\Environment\PaypalEnv;
use PrestaShop\Module\PrestashopCheckout\PayPal\PayPalConfiguration;
use PrestaShop\Module\PrestashopCheckout\Repository\PaypalAccountRepository;

class CheckoutServiceConfiguration
{
    /**
     * @var PaypalAccountRepository
     */
    private $paypalAccountRepository;

    /**
     * @var PayPalConfiguration
     */
    private $paypalConfiguration;

    /**
     * @param PaypalAccountRepository $paypalAccountRepository
     * @param PayPalConfiguration paypalConfiguration
     */
    public function __construct(
        PaypalAccountRepository $paypalAccountRepository,
        PayPalConfiguration $paypalConfiguration
    ) {
        $this->paypalAccountRepository = $paypalAccountRepository;
        $this->paypalConfiguration = $paypalConfiguration;
    }

    private function getPaypalCommit()
    {
        $controller = \Context::getContext()->controller;
        $pageName = !empty($controller) && isset($controller->php_self) ? $controller->php_self : '';

        return 'order' === $pageName ? 'true' : 'false';
    }

    public function getPaypalComponents() {
        $components = [
            'buttons',
            'funding-eligibility',
        ];

        if ($this->paypalAccountRepository->cardHostedFieldsIsAvailable()) {
            $components[] = 'hosted-fields';
        }

        return $components;
    }

    public function getPaypalDataClientToken() {
        $apiOrder = new Order(\Context::getContext()->link);
        $response = $apiOrder->generateClientToken($this->paypalAccountRepository->getMerchantId());

        if (empty($response['body']) || empty($response['body']['client_token'])) {
            throw new Exception('Unable to retrieve PayPal Client Token');
        }

        return $response['body']['client_token'];
    }

    public function getPaypalHostedFieldsEnabled() {
        return $this->paypalConfiguration->isCardPaymentEnabled() &&
            $this->paypalAccountRepository->cardHostedFieldsIsAllowed();
    }

    public function getConfiguration() {
        $config = [
            'paypal' => [
                'clientId' => (new PaypalEnv())->getPaypalClientId(),
                'components' => $this->getPaypalComponents(),
                'merchantId' => $this->paypalAccountRepository->getMerchantId(),
                'currency' => \Context::getContext()->currency->iso_code,
                'commit' => $this->getPaypalCommit(),
                'integrationDate' => $this->paypalConfiguration->getIntegrationDate(),
                'intent' => strtolower($this->paypalConfiguration->getIntent()),
                'vault' => false,
                'dataClientToken' => $this->getPaypalDataClientToken(),
            ],

            'hostedFields' => [
                'enabled' => $this->getPaypalHostedFieldsEnabled(),
            ],
        ];

        $paymentButton = $this->paypalConfiguration->getButtonConfiguration();
        if (!empty($paymentButton)) {
            $config['paymentButton'] = $paymentButton;
        }

        return $config;
    }
}
