<?php
/**
* 2007-2019 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author    PrestaShop SA <contact@prestashop.com>
*  @copyright 2007-2019 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

namespace PrestaShop\Module\PrestashopCheckout\Presenter\Store;

use PrestaShop\Module\PrestashopCheckout\Presenter\Store\Modules\PsxModule;
use PrestaShop\Module\PrestashopCheckout\Presenter\Store\Modules\PaypalModule;
use PrestaShop\Module\PrestashopCheckout\Presenter\Store\Modules\ContextModule;
use PrestaShop\Module\PrestashopCheckout\Presenter\Store\Modules\FirebaseModule;
use PrestaShop\Module\PrestashopCheckout\Presenter\Store\Modules\ConfigurationModule;

/**
 * Present the store to the vuejs app (vuex)
 */
class StorePresenter implements StorePresenterInterface
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
