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

namespace PsCheckout\Core\Settings\Configuration;

class LoggerConfiguration
{
    const PS_CHECKOUT_LOGGER_MAX_FILES_DEFAULT = 15;

    const PS_CHECKOUT_LOGGER_MAX_FILES = 'PS_CHECKOUT_LOGGER_MAX_FILES';

    const PS_CHECKOUT_LOGGER_LEVEL = 'PS_CHECKOUT_LOGGER_LEVEL';

    const PS_CHECKOUT_LOGGER_HTTP = 'PS_CHECKOUT_LOGGER_HTTP';

    const PS_CHECKOUT_LOGGER_HTTP_FORMAT = 'PS_CHECKOUT_LOGGER_HTTP_FORMAT';

    const LEVEL_DEBUG = 100;

    /**
     * Interesting events
     *
     * Examples: User logs in, SQL logs.
     */
    const LEVEL_INFO = 200;

    /**
     * Uncommon events
     */
    const LEVEL_NOTICE = 250;

    /**
     * Exceptional occurrences that are not errors
     *
     * Examples: Use of deprecated APIs, poor use of an API,
     * undesirable things that are not necessarily wrong.
     */
    const LEVEL_WARNING = 300;

    /**
     * Runtime errors
     */
    const LEVEL_ERROR = 400;

    /**
     * Critical conditions
     *
     * Example: Application component unavailable, unexpected exception.
     */
    const LEVEL_CRITICAL = 500;

    /**
     * Action must be taken immediately
     *
     * Example: Entire website down, database unavailable, etc.
     * This should trigger the SMS alerts and wake you up.
     */
    const LEVEL_ALERT = 550;

    /**
     * Urgent alert.
     */
    const LEVEL_EMERGENCY = 600;
}
