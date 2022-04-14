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

namespace PrestaShop\Module\PrestashopCheckout\MarketPlace;

use Exception;

class GetMarketPlaceModuleDataQueryHandler
{
    /**
     * @var MarketPlaceModuleDataProvider
     */
    private $marketPlaceModuleDataProvider;

    /**
     * @param MarketPlaceModuleDataProvider $marketPlaceModuleDataProvider
     */
    public function __construct(MarketPlaceModuleDataProvider $marketPlaceModuleDataProvider)
    {
        $this->marketPlaceModuleDataProvider = $marketPlaceModuleDataProvider;
    }

    /**
     * @param GetMarketPlaceModuleDataQuery $query
     *
     * @return GetMarketPlaceModuleDataQueryResult
     *
     * @throws MarketPlaceException
     */
    public function handle(GetMarketPlaceModuleDataQuery $query)
    {
        try {
            $moduleData = $this->marketPlaceModuleDataProvider->getModuleData(
                $query->getModuleId(),
                $query->getPsVersion(),
                $query->getLanguageCode(),
                $query->getCountryCode()
            )['modules'][0];
        } catch (Exception $exception) {
            throw new MarketPlaceException('Unable to retrieve module data from marketplace', MarketPlaceException::CANNOT_RETRIEVE_MODULE_DATA, $exception);
        }

        return new GetMarketPlaceModuleDataQueryResult(
            $moduleData['id'],
            $moduleData['name'],
            $moduleData['version'],
            $moduleData['last_update'],
            $moduleData['changeLog']
        );
    }
}
