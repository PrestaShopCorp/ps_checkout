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

namespace PrestaShop\Module\PrestashopCheckout\Api\Firebase;

use PrestaShop\Module\PrestashopCheckout\Api\Firebase\Client\FirebaseClient;

/**
 * Handle authentication firebase requests
 */
class Auth extends FirebaseClient
{
    /**
     * Auth user with email & password
     *
     * @see https://firebase.google.com/docs/reference/rest/auth/#section-sign-in-email-password Firebase documentation
     *
     * @param string $email
     * @param string $password
     *
     * @return array|bool
     */
    public function signInWithEmailAndPassword($email, $password)
    {
        $this->setRoute('https://www.googleapis.com/identitytoolkit/v3/relyingparty/verifyPassword');

        return $this->post([
            'json' => [
                'email' => $email,
                'password' => $password,
                'returnSecureToken' => true,
            ],
        ]);
    }

    /**
     * Create user with email & password
     *
     * @see https://firebase.google.com/docs/reference/rest/auth#section-create-email-password Firebase documentation
     *
     * @param string $email
     * @param string $password
     *
     * @return array|bool
     */
    public function signUpWithEmailAndPassword($email, $password)
    {
        $this->setRoute('https://www.googleapis.com/identitytoolkit/v3/relyingparty/signupNewUser');

        return $this->post([
            'json' => [
                'email' => $email,
                'password' => $password,
                'returnSecureToken' => true,
            ],
        ]);
    }

    /**
     * Trigger email in order to reset password
     *
     * @see https://firebase.google.com/docs/reference/rest/auth#section-send-password-reset-email Firebase documentation
     *
     * @param string $email
     *
     * @return array|bool
     */
    public function sendPasswordResetEmail($email)
    {
        $this->setRoute('https://identitytoolkit.googleapis.com/v1/accounts:sendOobCode?key=' . $this->apiKey);

        return $this->post([
            'json' => [
                'email' => $email,
                'requestType' => 'PASSWORD_RESET',
            ],
        ]);
    }
}
