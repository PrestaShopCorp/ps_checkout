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

namespace PrestaShop\Module\PrestashopCheckout;

/**
 * Get the shop context
 */
class ShopContext
{
    /**
     * Check if the module is installed on ready or download
     *
     * @return bool
     */
    public function isReady()
    {
        return getenv('PLATEFORM') === 'PSREADY';
    }

    /**
     * Retrieve the bn code - if on ready send an empty bn code, the
     * server will replace it with the bn code for ready
     *
     * @return string
     */
    public function getBnCode()
    {
        $bnCode = 'PrestaShop_Cart_PSXO_PSDownload';

        if ($this->isReady()) { // if on ready send an empty bn-code
            $bnCode = '';
        }

        return $bnCode;
    }

    public function isShop17()
    {
        return version_compare(_PS_VERSION_, '1.7.0.0', '>=');
    }

    public function isShop171()
    {
        return version_compare(_PS_VERSION_, '1.7.1.0', '>=');
    }
}
