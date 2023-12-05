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

namespace PrestaShop\Module\PrestashopCheckout\Intent\ValueObject;

use PrestaShop\Module\PrestashopCheckout\Intent\Exception\IntentException;

class Intent
{
    /**
     * @var string
     */
    private $intent;

    const VALID_INTENT = ['CAPTURE', 'AUTHORIZE'];

    /**
     * @param string $intent
     *
     * @throws IntentException
     */
    public function __construct($intent)
    {
        $this->intent = $this->assertIsValidIntent($intent);
    }

    /**
     * @return string
     */
    public function getValue()
    {
        return $this->intent;
    }

    /**
     * @param $intent
     *
     * @return string
     *
     * @throws IntentException
     */
    public function assertIsValidIntent($intent)
    {
        if (!is_string($intent)) {
            throw new IntentException(sprintf('INTENT is not a string (%s => %s).', gettype($intent), var_export($intent, true)), IntentException::WRONG_TYPE_INTENT);
        }

        if (!in_array($intent, self::VALID_INTENT)) {
            throw new IntentException(sprintf('INTENT is not valid (%s => %s).', gettype($intent), var_export($intent, true)), IntentException::INVALID_INTENT);
        }

        return $intent;
    }
}
