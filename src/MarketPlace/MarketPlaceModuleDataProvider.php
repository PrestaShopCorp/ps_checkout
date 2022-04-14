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

namespace PrestaShop\Module\PrestashopCheckout\MarketPlace;

use GuzzleHttp\Client;
use Psr\SimpleCache\CacheInterface;

class MarketPlaceModuleDataProvider
{
    const BASE_URL = 'https://api.addons.prestashop.com/request/listing?';

    /**
     * @var \GuzzleHttp\Client
     */
    private $httpClient;

    /**
     * @var CacheInterface
     */
    private $cache;

    /**
     * @param \GuzzleHttp\Client $httpClient
     * @param CacheInterface $cache
     */
    public function __construct(
        Client $httpClient,
        CacheInterface $cache
    ) {
        $this->httpClient = $httpClient;
        $this->cache = $cache;
    }

    /**
     * @param int $getModuleId
     * @param string $getPsVersion
     * @param string $getLanguageCode
     * @param string $getCountryCode
     *
     * @return array
     */
    public function getModuleData($getModuleId, $getPsVersion, $getLanguageCode, $getCountryCode)
    {
        $cacheId = implode(
            '|',
            [
                $getModuleId,
                $getPsVersion,
                $getLanguageCode,
                $getCountryCode,
            ]
        );

        if ($this->cache->has($cacheId) && $this->cache->get($cacheId) !== null) {
            return $this->cache->get($cacheId);
        }

        $moduleData = $this->httpClient->get(
            static::BASE_URL .
            'version=' . $getPsVersion .
            '&iso_lang=' . $getLanguageCode .
            '&iso_code=' . $getCountryCode .
            '&format=json' .
            '&method=listing' .
            '&action=module' .
            '&id_module=' . $getModuleId
        );

        if (empty($moduleData)) {
            throw new MarketPlaceException('No module data received from marketplace', MarketPlaceException::EMPTY_MODULE_DATA);
        }

        $moduleData = json_decode($moduleData->getBody()->getContents(), true);

        $this->cache->set($cacheId, $moduleData);

        return $moduleData;
    }
}
