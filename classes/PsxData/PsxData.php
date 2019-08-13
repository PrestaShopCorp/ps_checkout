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

namespace PrestaShop\Module\PrestashopCheckout\PsxData;

/**
 * Check and set the merchant status
 */
class PsxData
{
    const MAASLAND_DATA_ROW_NAME = 'massland_client_data';

    /**
     * Get the MAASLAND_DATA_ROW_NAME data and return an array
     *
     * @return array|false false when doesn't exist
     */
    public function get()
    {
        $data = \Configuration::get(self::MAASLAND_DATA_ROW_NAME);

        if (false === $data) {
            return false;
        }

        return json_decode($data, true);
    }

    /**
     * Save the datas by updating or inserting the data to save
     *
     * @param array $dataToSave
     *
     * @return bool|array array if data errors
     */
    public function save($dataToSave)
    {
        $existingData = $this->get();

        if ($existingData === $dataToSave) {
            return true;
        }

        $errors = (new PsxDataValidation())->validateData($dataToSave);

        if (!empty($errors)) {
            return $errors;
        }

        return (bool) \Configuration::updateValue(self::MAASLAND_DATA_ROW_NAME, json_encode($dataToSave));
    }
}
