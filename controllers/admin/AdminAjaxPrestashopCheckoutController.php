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
use PrestaShop\Module\PrestashopCheckout\FirebaseClient;

class AdminAjaxPrestashopCheckoutController extends ModuleAdminController
{
    public function ajaxProcessSignIn()
    {
        $email = Tools::getValue('email');
        $password = Tools::getValue('password');

        $firebase = new FirebaseClient();

        try {
            $signIn = $firebase->signInWithEmailAndPassword($email, $password);
        } catch (\Exception $e) {
            PrestaShopLogger::addLog(sprintf($this->l('Failed login with Firebase: %s'), $e->getMessage()), 1);
            $this->ajaxDie(
                json_encode(
                    array(
                        'error' => true,
                        'message' => $e->getMessage(),
                    )
                )
            );

            return false;
        }

        $this->saveFirebaseAccountIfNoErrors($signIn);

        $this->ajaxDie(json_encode($signIn));
    }

    public function ajaxProcessSignUp()
    {
        $email = Tools::getValue('email');
        $password = Tools::getValue('password');

        $firebase = new FirebaseClient();

        try {
            $signUp = $firebase->signUpWithEmailAndPassword($email, $password);
        } catch (\Exception $e) {
            PrestaShopLogger::addLog(sprintf($this->l('Failed signup with Firebase: %s'), $e->getMessage()), 1);
            $this->ajaxDie(
                json_encode(
                    array(
                        'error' => true,
                        'message' => $e->getMessage(),
                    )
                )
            );

            return false;
        }

        $this->saveFirebaseAccountIfNoErrors($signUp);

        $this->ajaxDie(json_encode($signUp));
    }

    // TODO: replace save action by StoreManager.php class
    private function saveFirebaseAccountIfNoErrors($user)
    {
        if (false === isset($user['error'])) {
            Configuration::updateValue('PS_CHECKOUT_FIREBASE_EMAIL', $user['email']);
            Configuration::updateValue('PS_CHECKOUT_FIREBASE_ID_TOKEN', $user['idToken']);
            Configuration::updateValue('PS_CHECKOUT_FIREBASE_LOCAL_ID', $user['localId']);
            Configuration::updateValue('PS_CHECKOUT_FIREBASE_REFRESH_TOKEN', $user['refreshToken']);
        }
    }
}
