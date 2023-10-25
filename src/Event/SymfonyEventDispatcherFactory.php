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

namespace PrestaShop\Module\PrestashopCheckout\Event;

use PrestaShop\Module\PrestashopCheckout\Logger\LoggerConfiguration;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\Debug\TraceableEventDispatcher as SymfonyTraceableEventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcher as SymfonyEventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface as SymfonyEventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface as SymfonyEventSubscriberInterface;
use Symfony\Component\Stopwatch\Stopwatch as SymfonyStopwatch;

class SymfonyEventDispatcherFactory
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var LoggerConfiguration
     */
    private $configuration;

    /**
     * @param LoggerInterface $logger
     * @param LoggerConfiguration $configuration
     */
    public function __construct(LoggerInterface $logger, LoggerConfiguration $configuration)
    {
        $this->logger = $logger;
        $this->configuration = $configuration;
    }

    /**
     * @param SymfonyEventSubscriberInterface[] $eventSubscribers
     *
     * @return SymfonyEventDispatcherInterface
     */
    public function create(array $eventSubscribers)
    {
        $eventDispatcher = LoggerConfiguration::LEVEL_DEBUG === $this->configuration->getLevel()
            ? new SymfonyTraceableEventDispatcher(
                new SymfonyEventDispatcher(),
                new SymfonyStopwatch(),
                $this->logger
            )
            : new SymfonyEventDispatcher();

        foreach ($eventSubscribers as $eventSubscriber) {
            $eventDispatcher->addSubscriber($eventSubscriber);
        }

        return $eventDispatcher;
    }
}
