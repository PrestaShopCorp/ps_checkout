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

namespace PrestaShop\Module\PrestashopCheckout\Environment;

/**
 * Allow to set the differents api key / api link depending on
 */
class PsxEnv extends Env
{
    /**
     * Url api maasland (production live by default)
     *
     * @var string
     */
    private $psxApiUrl;

    public function __construct()
    {
        parent::__construct();

        $this->setPsxApiUrl($_ENV['PSX_API_URL']);
    }

    /**
     * getter for psxApiUrl
     */
    public function getPsxApiUrl()
    {
        return $this->psxApiUrl;
    }

    /**
     * setter for psxApiUrl
     *
     * @param string $url
     */
    private function setPsxApiUrl($url)
    {
        $this->psxApiUrl = $url;
    }
}
