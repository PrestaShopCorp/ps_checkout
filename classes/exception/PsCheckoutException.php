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

namespace PrestaShop\Module\PrestashopCheckout;

class PsCheckoutException extends \PrestaShopExceptionCore
{
    /**
     * Set the HTTP code returned
     *
     * @var int
     */
    const HTTP_CODE = 400;

    /**
     * Contain the error message or messages
     *
     * @var string|array
     */
    private $messages;

    /**
     * @param string|array $messages
     */
    public function __construct($messages)
    {
        parent::__construct();

        $this->messages = $messages;
    }

    /**
     * Get the array or string message and return an array
     *
     * @return array
     */
    public function getArrayMessages()
    {
        return (array) $this->messages;
    }

    /**
     * Get the HTTP error Code
     *
     * @return int
     */
    public function getHTTPCode()
    {
        return $this::HTTP_CODE;
    }
}
