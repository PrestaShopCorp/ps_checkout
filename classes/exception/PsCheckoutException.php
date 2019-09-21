<?php
/**
* 2007-2019 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Open Software License (OSL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/osl-3.0.php
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
* @author PrestaShop SA <contact@prestashop.com>
* @copyright 2007-2019 PrestaShop SA
* @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
* International Registered Trademark & Property of PrestaShop SA
**/

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
