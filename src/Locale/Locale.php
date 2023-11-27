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

namespace PrestaShop\Module\PrestashopCheckout\Locale;

use PrestaShop\Module\PrestashopCheckout\Locale\Exception\LocaleException;

class Locale
{
    /** @var string */
    private $code;

    /**
     * @param $code
     *
     * @throws LocaleException
     */
    public function __construct($code)
    {
        $this->code = $this->assertLocaleCodeIsValid($code);
    }

    /**
     * @return mixed
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param mixed $code
     */
    public function setCode($code)
    {
        $this->code = $code;
    }

    /**
     * @return string
     *
     * @throws LocaleException
     */
    private function assertLocaleCodeIsValid($code)
    {
        if (!is_string($code)) {
            throw new LocaleException(sprintf('CODE is not a string (%s)', gettype($code)), LocaleException::WRONG_TYPE_CODE);
        }
        if (preg_match('/^[A-Z]{2}$/', $code) === 0) {
            throw new LocaleException('Invalid code', LocaleException::INVALID_CODE);
        }

        return $code;
    }
}
