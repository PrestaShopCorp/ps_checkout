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

namespace PrestaShop\Module\PrestashopCheckout\FundingSource;

class FundingSourceEntity
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var int
     */
    private $position;

    /**
     * @var array
     */
    private $countries = [];

    /**
     * @var bool
     */
    private $isEnabled = true;

    /**
     * @var bool
     */
    private $isToggleable = true;

    /**
     * @param string $name
     */
    public function __construct($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return int
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * @return array
     */
    public function getCountries()
    {
        return $this->countries;
    }

    /**
     * @return bool
     */
    public function getIsEnabled()
    {
        return $this->isEnabled;
    }

    /**
     * @return bool
     */
    public function getIsToggleable()
    {
        return $this->isToggleable;
    }

    /**
     * @param int $position
     *
     * @return void
     */
    public function setPosition($position)
    {
        $this->position = $position;
    }

    /**
     * @param array $countries
     *
     * @return void
     */
    public function setCountries($countries)
    {
        $this->countries = $countries;
    }

    /**
     * @param bool $isEnabled
     *
     * @return void
     */
    public function setIsEnabled($isEnabled)
    {
        $this->isEnabled = $isEnabled;
    }

    /**
     * @param bool $isToggleable
     *
     * @return void
     */
    public function setIsToggleable($isToggleable)
    {
        $this->isToggleable = $isToggleable;
    }
}
