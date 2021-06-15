<?php
/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0  Academic Free License (AFL 3.0)
 */

namespace PrestaShop\Module\PrestashopCheckout\Session;

class SessionManager
{
    /**
     * @var \PrestaShop\Module\PrestashopCheckout\Session\SessionRepositoryInterface
     */
    private $repository;

    /**
     * @param \PrestaShop\Module\PrestashopCheckout\Session\SessionRepositoryInterface $repository
     *
     * @return void
     */
    public function __construct(SessionRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Open an user session
     *
     * @param array $sessionData
     *
     * @return \PrestaShop\Module\PrestashopCheckout\Session\Session
     */
    public function open(array $sessionData)
    {
        $this->repository->save($sessionData);

        return $this->get($sessionData);
    }

    /**
     * Get an user session
     *
     * @param array $sessionData
     *
     * @return \PrestaShop\Module\PrestashopCheckout\Session\Session|null
     */
    public function get(array $sessionData)
    {
        $session = $this->repository->get($sessionData);

        if ($session && SessionHelper::isExpired($session)) {
            $this->close($session);

            return null;
        }

        return $session;
    }

    /**
     * Update an user session
     *
     * @param \PrestaShop\Module\PrestashopCheckout\Session\Session $session
     *
     * @return bool
     */
    public function update(Session $session)
    {
        return $this->repository->update($session);
    }

    /**
     * Close an user session
     *
     * @param \PrestaShop\Module\PrestashopCheckout\Session\Session $session
     *
     * @return bool
     */
    public function close(Session $session)
    {
        return $this->repository->close($session->getUserId(), $session->getShopId(), $session->getIsClosed());
    }
}
