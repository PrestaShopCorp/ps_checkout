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

namespace PrestaShop\Module\PrestashopCheckout\Presenter\Store\Modules;

use PrestaShop\Module\PrestashopCheckout\Api\Firebase\Token;
use PrestaShop\Module\PrestashopCheckout\Presenter\PresenterInterface;

/**
 * Construct the firebase module
 */
class FirebaseModule implements PresenterInterface
{
    /**
     * Present the Firebase module (vuex)
     *
     * @return array
     */
    public function present()
    {
        $idToken = (new Token())->getToken();

        $firebaseModule = [
            'firebase' => [
                'email' => \Configuration::get(
                    'PS_PSX_FIREBASE_EMAIL',
                    null,
                    null,
                    (int) \Context::getContext()->shop->id),
                'idToken' => $idToken,
                'localId' => \Configuration::get(
                    'PS_PSX_FIREBASE_LOCAL_ID',
                    null,
                    null,
                    (int) \Context::getContext()->shop->id),
                'refreshToken' => \Configuration::get(
                    'PS_PSX_FIREBASE_REFRESH_TOKEN',
                    null,
                    null,
                    (int) \Context::getContext()->shop->id),
                'onboardingCompleted' => !empty($idToken),
            ],
        ];

        return $firebaseModule;
    }
}
