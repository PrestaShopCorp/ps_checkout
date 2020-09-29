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

namespace PrestaShop\Module\PrestashopCheckout\Exception;

use PrestaShop\Module\PrestashopCheckout\PayPal\Mode;
use PrestaShop\Module\PrestashopCheckout\PayPal\PayPalConfiguration;

class CustomerExceptionConverter
{
    const customerMessages = [
        PayPalException::ORDER_ALREADY_CAPTURED => 'Order cannot be saved',
        PsCheckoutException::PRESTASHOP_PAYMENT_UNAVAILABLE => 'This payment method is unavailable',
        PsCheckoutException::PSCHECKOUT_HTTP_EXCEPTION => 'Unable to call API',
        PsCheckoutException::PAYPAL_ORDER_IDENTIFIER_MISSING => 'PayPal order identifier is missing',
        PsCheckoutException::PAYPAL_PAYMENT_METHOD_MISSING => 'PayPal payment method is missing',
        PsCheckoutException::PRESTASHOP_CONTEXT_INVALID => 'Cart is invalid',
    ];

    const defaultMessage = 'Error processing payment';

    /**
     * @var PayPalConfiguration
     */
    private $configuration;

    /**
     * @param PayPalConfiguration $configuration
     */
    public function __construct(PayPalConfiguration $configuration)
    {
        $this->configuration = $configuration;
    }

    /**
     * @param \Exception $exception
     *
     * @return string
     */
    public function getCustomerMessage(\Exception $exception)
    {
        if ($this->configuration->getPaymentMode() === Mode::SANDBOX) {
            return $exception->getMessage();
        }

        if (in_array($exception->getCode(), self::customerMessages)) {
            return self::customerMessages[$exception->getCode()];
        }

        return self::defaultMessage;
    }
}
