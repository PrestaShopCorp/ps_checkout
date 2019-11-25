<?php
/**
 * 2007-2019 PrestaShop and Contributors
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
 * @copyright 2007-2019 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 * International Registered Trademark & Property of PrestaShop SA
 */
namespace PrestaShop\Module\PrestashopCheckout\Factory;

use Monolog\Logger;
use Monolog\Handler\RotatingFileHandler;

class CheckoutLogger
{
    const MAX_FILES = 7;
    
    public static function create()
    {
        $rotatingFileHandler = new RotatingFileHandler(
            _PS_ROOT_DIR_ . '/var/logs/' . _PS_MODE_DEV_ . '/ps_checkout',
            static::MAX_FILES
        );

        $logger = new Logger('global');
        $logger->pushHandler($rotatingFileHandler);

        return $logger;
    }
}
