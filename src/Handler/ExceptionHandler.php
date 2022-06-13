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

namespace PrestaShop\Module\PrestashopCheckout\Handler;

use Exception;
use PrestaShop\Module\PrestashopCheckout\Environment\SentryEnv;
use PrestaShop\Module\PrestashopCheckout\Repository\PsAccountRepository;
use Ps_checkout;
use Raven_Client;

class ExceptionHandler
{
    /**
     * @var Raven_Client
     */
    protected $client;

    /**
     * @param Ps_checkout $module
     * @param SentryEnv $sentryEnv
     * @param PsAccountRepository $psAccountRepository
     *
     * @throws \Raven_Exception
     */
    public function __construct(Ps_checkout $module, SentryEnv $sentryEnv, PsAccountRepository $psAccountRepository)
    {
        try {
            $this->client = $module->getSentryClient();

            if (empty($this->client)) {
                $this->client = new ModuleFilteredRavenClient(
                    $sentryEnv->getDsn(),
                    [
                        'level' => 'error',
                        'error_types' => E_ERROR,
                        'tags' => [
                            'php_version' => phpversion(),
                            'module_version' => $module->version,
                            'prestashop_version' => _PS_VERSION_,
                        ],
                    ]
                );

                $this->client->setAppPath(realpath(_PS_MODULE_DIR_ . 'ps_checkout/'));
                $this->client->setExcludedAppPaths([
                    realpath(_PS_MODULE_DIR_ . 'ps_checkout/vendor/'),
                ]);
                $this->client->setExcludedDomains(['127.0.0.1', 'localhost', '.local']);

                if (version_compare(phpversion(), '7.4.0', '>=') && version_compare(_PS_VERSION_, '1.7.8.0', '<')) {
                    return;
                }

                $this->client->install();
            }

            if ($psAccountRepository->onBoardingIsCompleted()) {
                $this->client->user_context([
                    'id' => $psAccountRepository->getLocalId(),
                    'email' => $psAccountRepository->getEmail(),
                ]);
            }
        } catch (Exception $exception) {
            $module->getLogger()->debug('Sentry exception', ['exception' => $exception]);
        }
    }

    /**
     * @param Exception $error
     * @param bool $throw
     * @param array|null $data
     *
     * @return void
     *
     * @throws Exception
     */
    public function handle(Exception $error, $throw = true, $data = null)
    {
        if (null !== $this->client) {
            $this->client->captureException($error, $data);
        }

        if ($throw) {
            throw $error;
        }
    }
}
