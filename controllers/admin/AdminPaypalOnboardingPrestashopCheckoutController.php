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

        if ($persistentConfiguration->savePaypalAccount($paypalAccount)) {
            // Update onboarding session
            /** @var \PrestaShop\Module\PrestashopCheckout\Session\Onboarding\OnboardingSessionManager $onboardingSessionManager */
            $onboardingSessionManager = $this->module->getService('ps_checkout.session.onboarding.manager');
            $openedSession = $onboardingSessionManager->getOpened();
            $data = json_decode($openedSession->getData());
            $data->shop->merchant_id = $idMerchant;
            $data->shop->permissions_granted = Tools::getValue('permissionsGranted');
            $data->shop->consent_status = Tools::getValue('consentStatus');
            $data->shop->risk_status = Tools::getValue('riskStatus');
            $data->shop->account_status = Tools::getValue('accountStatus');
            $data->shop->is_email_confirmed = Tools::getValue('isEmailConfirmed');

            $openedSession->setData(json_encode($data));
            $onboardingSessionManager->apply('onboard_paypal', $openedSession->toArray(true));
        }

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
