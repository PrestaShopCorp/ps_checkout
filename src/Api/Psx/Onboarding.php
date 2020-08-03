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

namespace PrestaShop\Module\PrestashopCheckout\Api\Psx;

use PrestaShop\Module\PrestashopCheckout\Api\Client\PsxClient;

class Onboarding extends PsxClient
{
    /**
     * Collect merchant data during PSX onboarding process. These data are intended to be shared across PSX services.
     *
     * @param array $data
     *
     * @return array returned by ResponseApiHandler class
     */
    public function setOnboardingMerchant(array $data)
    {
        $this->setRoute('/psx/onboarding/merchant');

        return $this->post([
            'json' => json_encode($data),
        ]);
    }
}
