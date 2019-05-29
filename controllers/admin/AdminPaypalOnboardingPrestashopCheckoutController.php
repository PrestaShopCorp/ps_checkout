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

class AdminPaypalOnboardingPrestashopCheckoutController extends ModuleAdminController
{
    public function init()
    {
        $idMerchant = Tools::getValue('merchantIdInPayPal');

        if (true === empty($idMerchant)) {
            throw new PrestaShopException('merchantId cannot be empty');
        }

        if (13 !== strlen($idMerchant)) {
            throw new PrestaShopException('merchantId length must be at least 13 characters long');
        }

        Configuration::updateValue('PS_CHECKOUT_PAYPAL_ID_MERCHANT', $idMerchant);

        Tools::redirect(
            $this->context->link->getAdminLink(
                'AdminModules',
                true,
                array(),
                array(
                    'configure' => 'ps_checkout'
                )
            )
        );
    }
}
