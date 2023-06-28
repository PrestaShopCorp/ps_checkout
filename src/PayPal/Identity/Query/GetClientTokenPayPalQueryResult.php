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

namespace PrestaShop\Module\PrestashopCheckout\PayPal\Identity\Query;

class GetClientTokenPayPalQueryResult
{
    /**
     * @var string
     */
    private $clientToken;

    /**
     * @var string
     */
    private $idToken;

    /**
     * @var int
     */
    private $expiresIn;

    /**
     * @var int
     */
    private $createdAt;

    /**
     * @param string $clientToken
     * @param string $idToken
     * @param int $expiresIn
     * @param int $createdAt
     */
    public function __construct($clientToken, $idToken, $expiresIn, $createdAt)
    {
        $this->clientToken = $clientToken;
        $this->idToken = $idToken;
        $this->expiresIn = $expiresIn;
        $this->createdAt = $createdAt;
    }

    /**
     * @return string
     */
    public function getClientToken()
    {
        return $this->clientToken;
    }

    /**
     * @return string
     */
    public function getIdToken()
    {
        return $this->idToken;
    }

    /**
     * @return int
     */
    public function getExpiresIn()
    {
        return $this->expiresIn;
    }

    /**
     * @return int
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }
}
