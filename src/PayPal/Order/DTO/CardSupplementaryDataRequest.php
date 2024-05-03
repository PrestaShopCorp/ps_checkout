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

namespace PrestaShop\Module\PrestashopCheckout\PayPal\Order\DTO;

class CardSupplementaryDataRequest
{
    /**
     * @var Level2CardProcessingDataRequest
     */
    private $level_2;
    /**
     * @var Level3CardProcessingDataRequest
     */
    private $level_3;

    /**
     * @return Level2CardProcessingDataRequest
     */
    public function getLevel2()
    {
        return $this->level_2;
    }

    /**
     * @param Level2CardProcessingDataRequest $level_2
     *
     * @return void
     */
    public function setLevel2(Level2CardProcessingDataRequest $level_2)
    {
        $this->level_2 = $level_2;
    }

    /**
     * @return Level3CardProcessingDataRequest
     */
    public function getLevel3()
    {
        return $this->level_3;
    }

    /**
     * @param Level3CardProcessingDataRequest $level_3
     *
     * @return void
     */
    public function setLevel3(Level3CardProcessingDataRequest $level_3)
    {
        $this->level_3 = $level_3;
    }
}
