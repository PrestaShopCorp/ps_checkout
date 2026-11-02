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

namespace PsCheckout\Core\Hook\Handlers;

use PsCheckout\Core\PayPal\Order\Action\ReconcileCanceledPayPalOrdersActionInterface;
use PsCheckout\Infrastructure\Adapter\ConfigurationInterface;
use Psr\Log\LoggerInterface;

/**
 * Runs the canceled order reconciliation from a frequently triggered hook, at most once per interval, so the
 * module needs no cron. The caller is expected to defer handle() until after the response is sent.
 */
class CanceledPayPalOrderReconciliationHookHandler implements HookHandlerInterface
{
    const LAST_RUN_CONFIGURATION_KEY = 'PS_CHECKOUT_CANCELED_ORDER_RECONCILIATION_LAST_RUN';

    const INTERVAL_SECONDS = 3600;

    /**
     * @var ConfigurationInterface
     */
    private $configuration;

    /**
     * @var ReconcileCanceledPayPalOrdersActionInterface
     */
    private $reconcileCanceledPayPalOrdersAction;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        ConfigurationInterface $configuration,
        ReconcileCanceledPayPalOrdersActionInterface $reconcileCanceledPayPalOrdersAction,
        LoggerInterface $logger
    ) {
        $this->configuration = $configuration;
        $this->reconcileCanceledPayPalOrdersAction = $reconcileCanceledPayPalOrdersAction;
        $this->logger = $logger;
    }

    /**
     * Claim the current run when the interval elapsed. Two requests racing on the same second may both claim it:
     * the reconciliation is idempotent, so this is harmless.
     *
     * @return bool
     */
    public function isDue(): bool
    {
        $now = time();
        $lastRun = $this->configuration->getInteger(self::LAST_RUN_CONFIGURATION_KEY);

        if ($now - $lastRun < self::INTERVAL_SECONDS) {
            return false;
        }

        return $this->configuration->set(self::LAST_RUN_CONFIGURATION_KEY, $now);
    }

    /**
     * {@inheritDoc}
     */
    public function handle(HookParamsInterface $params): ?HookHandlerResult
    {
        try {
            $resumed = $this->reconcileCanceledPayPalOrdersAction->execute();

            if ($resumed > 0) {
                $this->logger->info('Canceled PayPal order reconciliation resumed orders', ['count' => $resumed]);
            }
        } catch (\Throwable $exception) {
            $this->logger->error('Canceled PayPal order reconciliation failed', ['exception' => $exception]);
        }

        return null;
    }
}
