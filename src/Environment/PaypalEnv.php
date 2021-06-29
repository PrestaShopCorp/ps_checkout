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

namespace PrestaShop\Module\PrestashopCheckout\Environment;

use PrestaShop\Module\PrestashopCheckout\PayPal\Mode;

/**
 * Allow to set the differents api key / api link depending on
 */
class PaypalEnv extends Env
{
    /**
     * PayPal client ID (production live by default)
     *
     * @var string
     */
    private $paypalClientId;

    public function __construct()
    {
        parent::__construct();

        $this->setEnvDependingOnMode();
    }

    private function setEnvDependingOnMode()
    {
        $this->setPaypalClientId($this->getEnv('PAYPAL_CLIENT_ID_LIVE'));

        if (Mode::SANDBOX === $this->mode) {
            $this->setPaypalClientId($this->getEnv('PAYPAL_CLIENT_ID_SANDBOX'));
        }
    }

    /**
     * getter for paypalClientId
     */
    public function getPaypalClientId()
    {
        return $this->paypalClientId;
    }

    /**
     * setter for paypalClientId
     *
     * @param string $clientId
     */
    private function setPaypalClientId($clientId)
    {
        $this->paypalClientId = $clientId;
    }
}
