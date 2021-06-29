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

namespace PrestaShop\Module\PrestashopCheckout\Presenter\Store\Modules;

use PrestaShop\Module\PrestashopCheckout\Api\Firebase\Token;
use PrestaShop\Module\PrestashopCheckout\Configuration\PrestaShopConfiguration;
use PrestaShop\Module\PrestashopCheckout\Entity\PsAccount;
use PrestaShop\Module\PrestashopCheckout\Presenter\PresenterInterface;

/**
 * Construct the firebase module
 */
class FirebaseModule implements PresenterInterface
{
    /**
     * @var PrestaShopConfiguration
     */
    private $configuration;

    /**
     * @param PrestaShopConfiguration $configuration
     */
    public function __construct(PrestaShopConfiguration $configuration)
    {
        $this->configuration = $configuration;
    }

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
                'email' => $this->configuration->get(PsAccount::PS_PSX_FIREBASE_EMAIL),
                'idToken' => $idToken,
                'localId' => $this->configuration->get(PsAccount::PS_PSX_FIREBASE_LOCAL_ID),
                'refreshToken' => $this->configuration->get(PsAccount::PS_PSX_FIREBASE_REFRESH_TOKEN),
                'onboardingCompleted' => !empty($idToken),
            ],
        ];

        return $firebaseModule;
    }
}
