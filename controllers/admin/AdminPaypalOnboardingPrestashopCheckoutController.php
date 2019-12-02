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
                [],
                [
                    'configure' => 'ps_checkout',
                ]
            )
        );
    }
}
