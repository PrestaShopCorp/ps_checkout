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

namespace PsCheckout\Core\PayPal\Order\Cache;

use PsCheckout\Core\PayPal\OrderStatus\Action\PayPalCheckOrderStatusActionInterface;
use PsCheckout\Core\PayPal\OrderStatus\Configuration\PayPalOrderStatusConfiguration;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Cache\Adapter\ChainAdapter;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;

class PayPalOrderCache extends ChainAdapter implements PayPalOrderCacheInterface
{
    /**
     * @var PayPalCheckOrderStatusActionInterface
     */
    private $payPalCheckOrderStatusAction;

    public function __construct(
        ArrayAdapter $arrayCache,
        FilesystemAdapter $filesystemCache,
        PayPalCheckOrderStatusActionInterface $payPalCheckOrderStatusAction
    ) {
        parent::__construct([$filesystemCache, $arrayCache]);
        $this->payPalCheckOrderStatusAction = $payPalCheckOrderStatusAction;
    }

    const CACHE_TTL = [
        PayPalOrderStatusConfiguration::STATUS_CREATED => 600,
        PayPalOrderStatusConfiguration::STATUS_PAYER_ACTION_REQUIRED => 600,
        PayPalOrderStatusConfiguration::STATUS_APPROVED => 600,
        PayPalOrderStatusConfiguration::STATUS_VOIDED => 3600,
        PayPalOrderStatusConfiguration::STATUS_SAVED => 3600,
        PayPalOrderStatusConfiguration::STATUS_CANCELED => 3600,
        PayPalOrderStatusConfiguration::STATUS_COMPLETED => 3600,
    ];

    /**
     */
    public function updateOrderCache($orderResponse)
    {
        $currentOrderPayPal = $this->getValue($orderResponse->getId());

        if ($currentOrderPayPal && !$this->payPalCheckOrderStatusAction->execute($currentOrderPayPal['status'], $orderResponse->getStatus())) {
            return;
        }

        $this->set($orderResponse->getId(), $orderResponse->toArray());
    }

    /**
     * {@inheritdoc}
     */
    public function has($key): bool
    {
        return parent::hasItem($key);
    }

    /**
     * {@inheritdoc}
     */
    public function getValue($key)
    {
        return parent::getItem($key)->get();
    }

    /**
     * {@inheritdoc}
     *
     * @return bool
     */
    public function set(string $key, $value, $ttl = null): bool
    {
        if (!$ttl && isset($value['status']) && isset(self::CACHE_TTL[$value['status']])) {
            $ttl = self::CACHE_TTL[$value['status']];
        }

        $cacheItem = $this->getItem($key)->set($value)->expiresAfter($ttl);

        return $this->save($cacheItem);
    }

    /**
     * {@inheritdoc}
     */
    public function delete(string $key): bool
    {
        return parent::deleteItem($key);
    }
}
