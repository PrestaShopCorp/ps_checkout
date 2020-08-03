<?php
/**
 * 2007-2020 PrestaShop and Contributors
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
 * @copyright 2007-2020 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 * International Registered Trademark & Property of PrestaShop SA
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
     * @return array
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
     * @return array
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
     * @return array
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
