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

namespace PrestaShop\Module\PrestashopCheckout\Repository;

use PrestaShop\Module\PrestashopCheckout\Entity\PsAccount;

/**
 * Repository for PsAccount class
 */
class PsAccountRepository
{
    /**
     * Get current onboarded prestashop account
     *
     * @return PsAccount
     */
    public function getOnboardedAccount()
    {
        $psAccount = new PsAccount(
            $this->getIdToken(),
            $this->getRefreshToken(),
            $this->getEmail(),
            $this->getLocalId(),
            $this->getPsxForm()
        );

        return $psAccount;
    }

    /**
     * Retrieve the status of the psx form : return true if the form is completed, otherwise return false.
     * If on ready, the merchant doesn't need to complete the form, so return true to act like if the
     * user complete the form
     *
     * @return bool
     */
    public function psxFormIsCompleted()
    {
        if (getenv('PLATEFORM') === 'PSREADY') { // if on ready, the user is already onboarded
            return true;
        }

        return !empty($this->getPsxForm());
    }

    /**
     * Get the status of the firebase onboarding
     * Only check idToken: is the only one truly mandatory
     *
     * @return bool
     */
    public function onbardingIsCompleted()
    {
        return !empty($this->getIdToken()) && $this->psxFormIsCompleted();
    }

    /**
     * Get firebase email from database
     *
     * @return string|bool
     */
    public function getEmail()
    {
        return \Configuration::get(PsAccount::PS_PSX_FIREBASE_EMAIL);
    }

    /**
     * Get firebase idToken from database
     *
     * @return string|bool
     */
    public function getIdToken()
    {
        return \Configuration::get(PsAccount::PS_PSX_FIREBASE_ID_TOKEN);
    }

    /**
     * Get firebase localId from database
     *
     * @return string|bool
     */
    public function getLocalId()
    {
        return \Configuration::get(PsAccount::PS_PSX_FIREBASE_LOCAL_ID);
    }

    /**
     * Get firebase refreshToken from database
     *
     * @return string|bool
     */
    public function getRefreshToken()
    {
        return \Configuration::get(PsAccount::PS_PSX_FIREBASE_REFRESH_TOKEN);
    }

    /**
     * Get psx form from database
     *
     * @param bool $toArray
     *
     * @return string|bool|array
     */
    public function getPsxForm($toArray = false)
    {
        if ($toArray) {
            return json_decode(\Configuration::get('PS_CHECKOUT_PSX_FORM'), true);
        }

        return \Configuration::get(PsAccount::PS_CHECKOUT_PSX_FORM);
    }
}
