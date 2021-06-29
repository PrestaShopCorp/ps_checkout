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

namespace PrestaShop\Module\PrestashopCheckout\Presenter\Store;

use PrestaShop\Module\PrestashopCheckout\Presenter\PresenterInterface;

/**
 * Present the store to the vuejs app (vuex)
 */
class StorePresenter implements PresenterInterface
{
    /**
     * @var PresenterInterface[]
     */
    private $presenters;

    /**
     * @var array
     */
    private $store;

    /**
     * @param PresenterInterface[] $presenters
     * @param array $store
     */
    public function __construct($presenters, array $store = [])
    {
        // Allow to set a custom store for tests purpose
        if (null !== $store) {
            $this->store = $store;
        }

        $this->presenters = $presenters;
    }

    /**
     * Build the store required by vuex
     *
     * @return array
     */
    public function present()
    {
        if ([] !== $this->store) {
            return $this->store;
        }

        foreach ($this->presenters as $presenter) {
            if ($presenter instanceof PresenterInterface) {
                $this->store = array_merge($this->store, $presenter->present());
            }
        }

        return $this->store;
    }
}
