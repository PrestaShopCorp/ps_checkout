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

class Name
{
    /**
     * @var string
     */
    private $prefix;
    /**
     * @var string
     */
    private $given_name;
    /**
     * @var string
     */
    private $surname;
    /**
     * @var string
     */
    private $middle_name;
    /**
     * @var string
     */
    private $suffix;
    /**
     * @var string
     */
    private $alternate_full_name;
    /**
     * @var string
     */
    private $full_name;

    /**
     * @return string
     */
    public function getPrefix()
    {
        return $this->prefix;
    }

    /**
     * @param string $prefix
     *
     * @return self
     */
    public function setPrefix($prefix)
    {
        $this->prefix = $prefix;

        return $this;
    }

    /**
     * @return string
     */
    public function getGivenName()
    {
        return $this->given_name;
    }

    /**
     * @param string $given_name
     *
     * @return self
     */
    public function setGivenName($given_name)
    {
        $this->given_name = $given_name;

        return $this;
    }

    /**
     * @return string
     */
    public function getSurname()
    {
        return $this->surname;
    }

    /**
     * @param string $surname
     *
     * @return self
     */
    public function setSurname($surname)
    {
        $this->surname = $surname;

        return $this;
    }

    /**
     * @return string
     */
    public function getMiddleName()
    {
        return $this->middle_name;
    }

    /**
     * @param string $middle_name
     *
     * @return self
     */
    public function setMiddleName($middle_name)
    {
        $this->middle_name = $middle_name;

        return $this;
    }

    /**
     * @return string
     */
    public function getSuffix()
    {
        return $this->suffix;
    }

    /**
     * @param string $suffix
     *
     * @return self
     */
    public function setSuffix($suffix)
    {
        $this->suffix = $suffix;

        return $this;
    }

    /**
     * @return string
     */
    public function getAlternateFullName()
    {
        return $this->alternate_full_name;
    }

    /**
     * @param string $alternate_full_name
     *
     * @return self
     */
    public function setAlternateFullName($alternate_full_name)
    {
        $this->alternate_full_name = $alternate_full_name;

        return $this;
    }

    /**
     * @return string
     */
    public function getFullName()
    {
        return $this->full_name;
    }

    /**
     * @param string $full_name
     *
     * @return self
     */
    public function setFullName($full_name)
    {
        $this->full_name = $full_name;

        return $this;
    }
}
