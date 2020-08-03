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

namespace PrestaShop\Module\PrestashopCheckout\Presenter\Store;

use PrestaShop\Module\PrestashopCheckout\Exception\PsCheckoutException;
use PrestaShop\Module\PrestashopCheckout\Presenter\PresenterInterface;
use PrestaShop\Module\PrestashopCheckout\Presenter\Store\Modules\ConfigurationModule;
use PrestaShop\Module\PrestashopCheckout\Presenter\Store\Modules\ContextModule;
use PrestaShop\Module\PrestashopCheckout\Presenter\Store\Modules\FirebaseModule;
use PrestaShop\Module\PrestashopCheckout\Presenter\Store\Modules\PaypalModule;
use PrestaShop\Module\PrestashopCheckout\Presenter\Store\Modules\PsxModule;

/**
 * Present the store to the vuejs app (vuex)
 */
class StorePresenter implements PresenterInterface
{
    /**
     * @var \Module
     */
    private $module;

    /**
     * @var \Context
     */
    private $context;

    /**
     * @var array
     */
    private $store;

    public function __construct(\Module $module, \Context $context, array $store = null)
    {
        // Allow to set a custom store for tests purpose
        if (null !== $store) {
            $this->store = $store;
        }

        $this->module = $module;
        $this->context = $context;
    }

    /**
     * Build the store required by vuex
     *
     * @return array
     *
     * @throws PsCheckoutException
     */
    public function present()
    {
        if (null !== $this->store) {
            return $this->store;
        }

        $this->store = array_merge(
            (new ContextModule($this->module, $this->context))->present(),
            (new FirebaseModule())->present(),
            (new PaypalModule())->present(),
            (new PsxModule($this->context))->present(),
            (new ConfigurationModule())->present()
        );

        return $this->store;
    }
}
