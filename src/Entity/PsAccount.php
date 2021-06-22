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

namespace PrestaShop\Module\PrestashopCheckout\Entity;

use PrestaShop\Module\PrestashopCheckout\Exception\PsCheckoutException;

/**
 * Not really an entity.
 * Allow to manage data regarding firebase account in database
 */
class PsAccount
{
    /**
     * Const list of databse fields used for store data
     */
    const PS_PSX_FIREBASE_EMAIL = 'PS_PSX_FIREBASE_EMAIL';
    const PS_PSX_FIREBASE_ID_TOKEN = 'PS_PSX_FIREBASE_ID_TOKEN';
    const PS_PSX_FIREBASE_LOCAL_ID = 'PS_PSX_FIREBASE_LOCAL_ID';
    const PS_PSX_FIREBASE_REFRESH_TOKEN = 'PS_PSX_FIREBASE_REFRESH_TOKEN';
    const PS_CHECKOUT_PSX_FORM = 'PS_CHECKOUT_PSX_FORM';
    const PS_CHECKOUT_SHOP_UUID_V4 = 'PS_CHECKOUT_SHOP_UUID_V4';

    /**
     * Firebase email
     *
     * @var string
     */
    private $email;

    /**
     * Firebase id token
     *
     * @var string
     */
    private $idToken;

    /**
     * Firebase local id
     *
     * @var string
     */
    private $localId;

    /**
     * Firebase refresh token
     *
     * @var string
     */
    private $refreshToken;

    /**
     * Psx Form (used to complete the onboarding)
     *
     * @var array
     */
    private $psxForm;

    /**
     * PsAccount constructor.
     *
     * @param string|null $idToken
     * @param string|null $refreshToken
     * @param string|null $email
     * @param string|null $localId
     * @param array|null $psxForm
     *
     * @throws PsCheckoutException
     */
    public function __construct($idToken = null, $refreshToken = null, $email = null, $localId = null, $psxForm = null)
    {
        $this->setIdToken($idToken);
        $this->setRefreshToken($refreshToken);

        if (null !== $localId) {
            $this->setLocalId($localId);
        }

        if (null !== $email) {
            $this->setEmail($email);
        }

        if (null !== $psxForm) {
            $this->setPsxForm($psxForm);
        }
    }

    /**
     * @param string $email
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }

    /**
     * @param string $idToken
     *
     * @throws PsCheckoutException
     */
    public function setIdToken($idToken)
    {
        if (empty($idToken)) {
            throw new PsCheckoutException('idToken cannot be empty', PsCheckoutException::PSACCOUNT_TOKEN_MISSING);
        }

        $this->idToken = $idToken;
    }

    /**
     * @param string $localId
     */
    public function setLocalId($localId)
    {
        $this->localId = $localId;
    }

    /**
     * @param string $refreshToken
     *
     * @throws PsCheckoutException
     */
    public function setRefreshToken($refreshToken)
    {
        if (empty($refreshToken)) {
            throw new PsCheckoutException('refreshToken cannot be empty', PsCheckoutException::PSACCOUNT_REFRESH_TOKEN_MISSING);
        }

        $this->refreshToken = $refreshToken;
    }

    /**
     * @param mixed $form
     */
    public function setPsxForm($form)
    {
        $this->psxForm = $form;
    }

    /**
     * getter $email
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * getter $idToken
     */
    public function getIdToken()
    {
        return $this->idToken;
    }

    /**
     * getter $localId
     */
    public function getLocalId()
    {
        return $this->localId;
    }

    /**
     * getter $refreshToken
     */
    public function getRefreshToken()
    {
        return $this->refreshToken;
    }

    /**
     * getter $psxForm
     */
    public function getPsxForm()
    {
        return $this->psxForm;
    }
}
