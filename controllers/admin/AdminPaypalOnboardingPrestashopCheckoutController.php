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
use PrestaShop\Module\PrestashopCheckout\Entity\PaypalAccount;
use PrestaShop\Module\PrestashopCheckout\Updater\PaypalAccountUpdater;

class AdminPaypalOnboardingPrestashopCheckoutController extends ModuleAdminController
{
    public function init()
    {
        $idMerchant = Tools::getValue('merchantIdInPayPal');

        if (true === empty($idMerchant)) {
            throw new PrestaShopException('merchantId cannot be empty');
        }

        if (PaypalAccountUpdater::MIN_ID_LENGTH > strlen($idMerchant)) {
            throw new PrestaShopException('merchantId length must be at least 13 characters long');
        }

        $paypalAccount = new PaypalAccount($idMerchant);
        $paypalAccount = (new PaypalAccountUpdater($paypalAccount))->update();

        if (false === $paypalAccount) {
            throw new PrestaShopException('A problem occured when updating the paypal account');
        }

        Tools::redirect(
            $this->context->link->getAdminLink(
                'AdminModules',
                true,
                array(),
                array(
                    'configure' => 'ps_checkout',
                )
            )
        );
    }
}
