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

use PrestaShop\Module\PrestashopCheckout\Environment\SentryEnv;

/**
 * Class SentryHandler used it to create the sentry client
 */
class SentryHandler
{
    private $client;

    public function __construct(SentryEnv $sentryEnv)
    {
        $this->client =
            new \Raven_Client(
                'https://'
                . $sentryEnv->getKey()
                . '@'
                . $sentryEnv->getOrganisation()
                . '.ingest.sentry.io/'
                . $sentryEnv->getProject()
            );
    }

    /**
     * @return \Raven_Client
     */
    public function getClient()
    {
        return $this->client;
    }
}
