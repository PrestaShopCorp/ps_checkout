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

namespace PrestaShop\Module\PrestashopCheckout\Faq;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use Prestashop\ModuleLibGuzzleAdapter\ClientFactory;

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
        $client = (new ClientFactory())->getClient([
            'base_url' => 'https://api.addons.prestashop.com/request/faq/',
            'timeout' => 10,
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
            $response = $this->client->sendRequest(
                new Request('POST', $this->generateRoute())
            );
        } catch (\Exception $exception) {
            /** @var \Ps_checkout $module */
            $module = \Module::getInstanceByName('ps_checkout');
            $module->getLogger()->error(
                'FAQ Exception ' . $exception->getCode(),
                [
                    'exception' => $exception,
                ]
            );

            return false;
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
