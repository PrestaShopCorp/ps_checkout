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

use PrestaShop\Module\PrestashopCheckout\Api\Maasland;

/**
 * Allow to instantiate a paypal order
 */
class PaypalOrder
{
    private $order = null;

    public function __construct(string $id)
    {
        $this->loadOrder($id);
    }

    /**
     * Load paypal order data
     */
    private function loadOrder($id)
    {
        $order = (new Maasland(\Context::getContext()->link))->fetchOrder($id);

        if (false === $order) {
            return false;
        }

        $this->setOrder($order);
    }

    /**
     * getter for the order
     *
     * // DOGE array or null
     * @return array
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * setter for order
     *
     * @param array $order
     */
    // DOGE: add array in param: `array $order`
    public function setOrder($order)
    {
        $this->order = $order;
    }
}
