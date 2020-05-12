<?php
/**
 * 2007-2020 PrestaShop and Contributors
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License 3.0 (AFL-3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2020 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 * International Registered Trademark & Property of PrestaShop SA
 */

namespace PrestaShop\Module\PrestashopCheckout\Faq;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

/**
 * Retrieve the faq of the module
 */
class Faq
{
    /**
     * Module key to identify on which module we will retrieve the faq
     *
     * @var string
     */
    private $moduleKey;

    /**
     * The version of PrestaShop
     *
     * @var string
     */
    private $psVersion;

    /**
     * In which language the faq will be retrieved
     *
     * @var string
     */
    private $isoCode;

    private $client;

    public function __construct()
    {
        $client = new Client([
            'base_url' => 'https://api.addons.prestashop.com/request/faq/',
            'defaults' => [
                'timeout' => 10,
            ],
        ]);

        $this->client = $client;
    }

    /**
     * Wrapper of method post from guzzle client
     *
     * @return array|bool return response or false if no response
     */
    public function getFaq()
    {
        try {
            $response = $this->client->post($this->generateRoute());
        } catch (RequestException $e) {
            /** @var \Ps_checkout $module */
            $module = \Module::getInstanceByName('ps_checkout');
            $module->getLogger()->error($e->getMessage());

            if (!$e->hasResponse()) {
                return false;
            }
            $response = $e->getResponse();
        }

        $data = json_decode($response->getBody(), true);

        return isset($data['categories']) && !empty($data['categories']) ? $data : false;
    }

    /**
     * Generate the route to retrieve the faq
     *
     * @return string route
     */
    public function generateRoute()
    {
        return $this->getModuleKey() . '/' . $this->getPsVersion() . '/' . $this->getIsoCode();
    }

    /**
     * Setter moduleKey
     *
     * @param string $moduleKey
     */
    public function setModuleKey($moduleKey)
    {
        $this->moduleKey = $moduleKey;
    }

    /**
     * Setter psVersion
     *
     * @param string $psVersion
     */
    public function setPsVersion($psVersion)
    {
        $this->psVersion = $psVersion;
    }

    /**
     * Setter isoCode
     *
     * @param string $isoCode
     */
    public function setIsoCode($isoCode)
    {
        $this->isoCode = $isoCode;
    }

    /**
     * Getter isoCode
     */
    public function getIsoCode()
    {
        return $this->isoCode;
    }

    /**
     * Getter psVersion
     */
    public function getPsVersion()
    {
        return $this->psVersion;
    }

    /**
     * Getter moduleKey
     */
    public function getModuleKey()
    {
        return $this->moduleKey;
    }
}
