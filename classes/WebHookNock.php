<?php
/**
 * 2007-2019 PrestaShop and Contributors
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
 * @copyright 2007-2019 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 * International Registered Trademark & Property of PrestaShop SA
 */

namespace PrestaShop\Module\PrestashopCheckout;

class WebHookNock
{
    /**
     * Return headers for PSL
     *
     * @param int $headerCode
     * @param array $headerDatas
     */
    public function setHeader($headerCode, array $headerDatas)
    {
        http_response_code($headerCode);
        header('Content-Type: application/json');
        headers_list();

        $bodyReturn = \Tools::jsonEncode($headerDatas);
        \PrestaShopLoggerCore::addLog('[PSPwebhook] ' . $bodyReturn, 3, null, null, null, true);

        echo $bodyReturn;
    }
}
