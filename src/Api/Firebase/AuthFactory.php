<?php
/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License version 3.0
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
 */

namespace PrestaShop\Module\PrestashopCheckout\Api\Firebase;

use PrestaShop\Module\PrestashopCheckout\Entity\PsAccount;
use PrestaShop\Module\PrestashopCheckout\PersistentConfiguration;

/**
 * Class AuthFactory used to interact between auth and db
 */
class AuthFactory
{
    /**
     * @var Auth
     */
    private $auth;

    /**
     * @var PersistentConfiguration
     */
    private $configuration;

    public function __construct(Auth $auth, PersistentConfiguration $configuration)
    {
        $this->auth = $auth;
        $this->configuration = $configuration;
    }

    /**
     * @param string $email
     * @param string $password
     *
     * @return array
     */
    public function signIn($email, $password)
    {
        $response = $this->auth->signInWithEmailAndPassword($email, $password);
        // if there is no error, save the account tokens in database
        if (true === $response['status']) {
            $this->savePsAccount($response);
        }

        return $response;
    }

    /**
     * @param string $email
     * @param string $password
     *
     * @return array
     */
    public function signUp($email, $password)
    {
        $response = $this->auth->signUpWithEmailAndPassword($email, $password);
        // if there is no error, save the account tokens in database
        if (true === $response['status']) {
            $this->savePsAccount($response);
        }

        return $response;
    }

    /**
     * @param string $email
     *
     * @return array
     */
    public function resetPassword($email)
    {
        return $this->auth->sendPasswordResetEmail($email);
    }

    /**
     * @param array $response
     *
     * @throws \PrestaShop\Module\PrestashopCheckout\Exception\PsCheckoutException
     */
    private function savePsAccount(array $response)
    {
        $psAccount = new PsAccount(
            $response['body']['idToken'],
            $response['body']['refreshToken'],
            $response['body']['email'],
            $response['body']['localId']
        );

        $this->configuration->savePsAccount($psAccount);
    }
}
