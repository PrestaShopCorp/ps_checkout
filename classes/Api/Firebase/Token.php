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

namespace PrestaShop\Module\PrestashopCheckout\Api\Firebase;

use PrestaShop\Module\PrestashopCheckout\Api\Firebase\Client\FirebaseClient;

/**
 * Handle authentication firebase requests
 */
class Token extends FirebaseClient
{
    /**
     * Refresh the token
     *
     * @see https://firebase.google.com/docs/reference/rest/auth#section-refresh-token Firebase documentation
     *
     * @return array
     */
    public function refresh()
    {
        $this->setRoute('https://securetoken.googleapis.com/v1/token');

        $response = $this->post([
            'json' => [
                'grant_type' => 'refresh_token',
                'refresh_token' => \Configuration::get('PS_PSX_FIREBASE_REFRESH_TOKEN'),
            ],
        ]);

        if (isset($response['id_token']) && isset($response['refresh_token'])) {
            \Configuration::updateValue('PS_PSX_FIREBASE_ID_TOKEN', $response['id_token']);
            \Configuration::updateValue('PS_PSX_FIREBASE_REFRESH_TOKEN', $response['refresh_token']);
        }

        return $response;
    }

    /**
     * Check the token validity. The token expire time is set to 3600 seconds.
     *
     * @return bool
     */
    public function checkIfTokenIsValid()
    {
        $query = 'SELECT date_upd
                FROM ' . _DB_PREFIX_ . 'configuration
                WHERE name="PS_PSX_FIREBASE_ID_TOKEN"
                AND date_upd > NOW() + INTERVAL 1 HOUR';

        $dateUpd = \Db::getInstance()->getValue($query);

        if (false === $dateUpd) {
            return false;
        }

        return true;
    }

    /**
     * Get the user firebase token
     *
     * @return string
     */
    public function getToken()
    {
        if (false === $this->checkIfTokenIsValid()) {
            $this->refresh();
        }

        return \Configuration::get('PS_PSX_FIREBASE_ID_TOKEN');
    }
}
