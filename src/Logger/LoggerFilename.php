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

namespace PrestaShop\Module\PrestashopCheckout\Logger;

use PrestaShop\Module\PrestashopCheckout\Exception\PsCheckoutException;

/**
 * Class responsible for returning log filename.
 */
class LoggerFilename
{
    /**
     * @var string Base filename
     */
    private $filename;

    /**
     * @var int Shop identifier
     */
    private $identifier;

    /**
     * @param string $filename
     * @param int $identifier
     *
     * @throws PsCheckoutException
     */
    public function __construct($filename, $identifier)
    {
        $this->assertNameIsValid($filename);
        $this->filename = $filename;
        $this->identifier = (int) $identifier;
    }

    /**
     * @return string
     */
    public function get()
    {
        return $this->filename . '-' . $this->identifier;
    }

    /**
     * @param string $name
     *
     * @throws PsCheckoutException
     */
    private function assertNameIsValid($name)
    {
        if (empty($name)) {
            throw new PsCheckoutException('Logger filename cannot be empty.', PsCheckoutException::UNKNOWN);
        }

        if (!is_string($name)) {
            throw new PsCheckoutException('Logger filename should be a string.', PsCheckoutException::UNKNOWN);
        }

        if (!preg_match('/^[a-zA-Z0-9_-]+$/', $name)) {
            throw new PsCheckoutException('Logger filename is invalid.', PsCheckoutException::UNKNOWN);
        }
    }
}
