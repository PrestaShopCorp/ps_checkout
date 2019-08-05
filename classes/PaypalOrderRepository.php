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

use PrestaShop\Module\PrestashopCheckout\Entity\OrderMatrice;

/**
 * Retrieve paypal order data from database
 */
class PaypalOrderRepository
{
    /**
     * Return PrestaShop order id for the given Paypal order ID
     *
     * @param int $paypalOrderId Order ID paypal
     *
     * @return int Order ID prestashop
     */
    public function getPsOrderIdByPaypalOrderId($paypalOrderId)
    {
        return (new OrderMatrice())->getOrderPrestashopFromPaypal($paypalOrderId);
    }

    /**
     * Return Paypal order ID for the given PrestaShop order reference
     *
     * @param string $prestashopOrderId
     *
     * @return string Order ID Paypal
     */
    public function getPaypalOrderIdByPsOrderRef($prestashopOrderId)
    {
        return (new OrderMatrice())->getOrderPaypalFromPrestashop($prestashopOrderId);
    }
}
