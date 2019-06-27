<?php
/**
* 2007-2019 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author    PrestaShop SA <contact@prestashop.com>
*  @copyright 2007-2019 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

namespace PrestaShop\Module\PrestashopCheckout;

/**
 * Allow to set the differents api key / api link depending on
 */
class Environment
{
    /**
     * Firebase public api key (production by default)
     *
     * @var string
     */
    private $firebaseApiKey = 'AIzaSyBEm26bA2KR893rY68enLdVGpqnkoW2Juo';

    /**
     * PayPal client ID (production live by default)
     *
     * @var string
     */
    private $paypalClientId = 'AXjYFXWyb4xJCErTUDiFkzL0Ulnn-bMm4fal4G-1nQXQ1ZQxp06fOuE7naKUXGkq2TZpYSiI9xXbs4eo';

    /**
     * Url api maasland (production live by default)
     *
     * @var string
     */
    private $maaslandUrl = 'https://api-live-checkout.psessentials.net/';

    public function __construct()
    {
        // if there is a custom config
        if (true === $this->isCustomized()) {
            $this->setCustomConf(); // set it
            return false;
        }

        if (false === $this->isLive()) {
            $this->setSandboxConf();
        }
    }

    /**
     * Check if the module is in integration or production
     *
     * @return boolean true if the module is in integration
     */
    private function isCustomized()
    {
        return file_exists(__DIR__ . '/../maaslandConf.json');
    }

    /**
     * Check if the module is in SANDBOX or LIVE mode
     *
     * @return boolean true if the module is in LIVE mode
     */
    private function isLive()
    {
        $mode = \Configuration::get('PS_CHECKOUT_MODE');

        if ('LIVE' === $mode) {
            return true;
        }

        return false;
    }

    /**
     * Override default production conf with production sandbox
     */
    private function setSandboxConf()
    {
        $this->setPaypalClientId('AWZMaFOTMPjG2oXFw1GqSp1hlrlFUTupuNqX0A0NJA_df0rcGQbyD9VwNAudXiRcAbSaePPPJ4FvgTqi');
        $this->setMaaslandUrl('https://api-sandbox-checkout.psessentials.net/');
    }

    /**
     * Set custom configuration (ex integration)
     */
    private function setCustomConf()
    {
        $conf = json_decode(file_get_contents(__DIR__ . '/../maaslandConf.json'));

        $this->setFirebaseApiKey($conf->integration->firebaseIntegrationApiKey);
        $this->setPaypalClientId('AXjYFXWyb4xJCErTUDiFkzL0Ulnn-bMm4fal4G-1nQXQ1ZQxp06fOuE7naKUXGkq2TZpYSiI9xXbs4eo');
        $this->setMaaslandUrl($conf->integration->maaslandLiveUrl);

        if (false === $this->isLive()) {
            $this->setFirebaseApiKey($conf->integration->firebaseIntegrationApiKey);
            $this->setPaypalClientId($conf->integration->paypalClientIdIntegrationSandbox);
            $this->setMaaslandUrl($conf->integration->maaslandSandboxUrl);
        }
    }

    /**
     * getter for firebaseApiKey
     */
    public function getFirebaseApiKey()
    {
        return $this->firebaseApiKey;
    }

    /**
     * getter for paypalClientId
     */
    public function getPaypalClientId()
    {
        return $this->paypalClientId;
    }

    /**
     * getter for maaslandUrl
     */
    public function getMaaslandUrl()
    {
        return $this->maaslandUrl;
    }

    /**
     * setter for firebaseApiKey
     *
     * @param string $apiKey
     */
    private function setFirebaseApiKey($apiKey)
    {
        $this->firebaseApiKey = $apiKey;
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

    /**
     * setter for maasland
     *
     * @param string $url
     */
    private function setMaaslandUrl($url)
    {
        $this->maaslandUrl = $url;
    }
}
