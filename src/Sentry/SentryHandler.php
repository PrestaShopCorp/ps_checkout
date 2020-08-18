<?php
/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0  Academic Free License (AFL 3.0)
 */

namespace PrestaShop\Module\PrestashopCheckout\Sentry;

use Monolog\Handler\RavenHandler;

/**
 * Class SentryHandler used it to handle sentry log
 */
class SentryHandler
{
    private $client;

    private $handler;

    public function __construct()
    {
        //`https://${process.env.VUE_APP_SENTRY_KEY}@${process.env.VUE_APP_SENTRY_ORGANIZATION}.ingest.sentry.io/${process.env.VUE_APP_SENTRY_PROJECT}`
        $this->client =
            new \Raven_Client(
                'https://'
                . $_ENV['VUE_APP_SENTRY_KEY']
                . '@'
                . $_ENV['VUE_APP_SENTRY_ORGANIZATION']
                . '.ingest.sentry.io/'
                . $_ENV['VUE_APP_SENTRY_PROJECT']
            );
        $this->handler = new RavenHandler($this->client);
    }

    /**
     * @return RavenHandler
     */
    public function getHandler()
    {
        return $this->handler;
    }
}
