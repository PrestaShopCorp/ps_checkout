<?php
/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License 3.0 (AFL-3.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 */
use PrestaShop\Module\PrestashopCheckout\Adapter\LinkAdapter;
use PrestaShop\Module\PrestashopCheckout\Entity\PaypalAccount;
use PrestaShop\Module\PrestashopCheckout\Updater\PaypalAccountUpdater;

class AdminPaypalOnboardingPrestashopCheckoutController extends ModuleAdminController
{
    /**
     * @var Ps_checkout
     */
    public $module;

    public function init()
    {
        parent::init();
        $idMerchant = Tools::getValue('merchantIdInPayPal');

        if (true === empty($idMerchant)) {
            throw new PrestaShopException('merchantId cannot be empty');
        }

        if (PaypalAccountUpdater::MIN_ID_LENGTH > strlen($idMerchant)) {
            throw new PrestaShopException('merchantId length must be at least 13 characters long');
        }

        $paypalAccount = new PaypalAccount($idMerchant);

        /** @var \PrestaShop\Module\PrestashopCheckout\PersistentConfiguration $persistentConfiguration */
        $persistentConfiguration = $this->module->getService('ps_checkout.persistent.configuration');
        $persistentConfiguration->savePaypalAccount($paypalAccount);

        /** @var PaypalAccountUpdater $accountUpdater */
        $accountUpdater = $this->module->getService('ps_checkout.updater.paypal.account');
        $accountUpdater->update($paypalAccount);

        if ($paypalAccount->getCardPaymentStatus() === PaypalAccountUpdater::SUBSCRIBED) {
            // track account paypal fully approved
            $this->module->getService('ps_checkout.segment.tracker')->track('Account Paypal Fully Approved', Shop::getContextListShopID());
        }

        Tools::redirect(
            (new LinkAdapter($this->context->link))->getAdminLink(
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
