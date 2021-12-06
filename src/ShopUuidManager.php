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

namespace PrestaShop\Module\PrestashopCheckout;

use PrestaShop\Module\PrestashopCheckout\Configuration\PrestaShopConfiguration;
use PrestaShop\Module\PrestashopCheckout\Exception\PsCheckoutException;
use PrestaShop\Module\PrestashopCheckout\Repository\ShopRepository;
use PrestaShop\Module\PrestashopCheckout\Shop\ShopProvider;
use Ramsey\Uuid\Uuid;

/**
 * Manage ShopUuid
 */
class ShopUuidManager
{
    /**
     * @var PrestaShopConfiguration
     */
    private $prestaShopConfiguration;
    /**
     * @var ShopProvider
     */
    private $shopProvider;
    /**
     * @var ShopRepository
     */
    private $shopRepository;

    public function __construct(
        PrestaShopConfiguration $prestaShopConfiguration,
        ShopProvider $shopProvider,
        ShopRepository $shopRepository
    ) {
        $this->prestaShopConfiguration = $prestaShopConfiguration;
        $this->shopProvider = $shopProvider;
        $this->shopRepository = $shopRepository;
    }

    /**
     * @param int $idShop
     *
     * @return string
     */
    public function getForShop($idShop)
    {
        return $this->prestaShopConfiguration->get('PS_CHECKOUT_SHOP_UUID_V4', ['id_shop' => (int) $idShop]);
    }

    /**
     * Used in hook ActionObjectShopAddAfter
     *
     * @param int $idShop
     *
     * @return bool
     *
     * @throws \Exception
     */
    public function generateForShop($idShop)
    {
        $result = true;

        if (false === $this->isSetForShop($idShop)) {
            $uuid4 = Uuid::uuid4();
            try {
                $this->prestaShopConfiguration->set(
                    'PS_CHECKOUT_SHOP_UUID_V4',
                    $uuid4->toString(),
                    ['id_shop' => (int)$idShop]
                );
            } catch (PsCheckoutException $exception) {
                $result = false;
            }
        }

        return $result;
    }

    public function setForShop($Uuid, $shopId)
    {
        $result = true;

        try {
            $this->prestaShopConfiguration->set(
                'PS_CHECKOUT_SHOP_UUID_V4',
                $Uuid,
                ['id_shop' => (int) $shopId]
            );
        } catch (PsCheckoutException $exception) {
            $result = false;
        }

        return $result;
    }

    /**
     * Used in module installation
     *
     * @return bool
     */
    public function generateForAllShops()
    {
        $result = true;

        foreach ($this->shopRepository->getShops(false, null, true) as $shopId) {
            $result = $result && $this->generateForShop($shopId);
        }

        return $result;
    }

    /**
     * @param int $idShop
     *
     * @return bool
     */
    public function isSetForShop($idShop)
    {
        if (true === $this->shopProvider->isFeatureActive()
            && false === $this->prestaShopConfiguration->has('PS_CHECKOUT_SHOP_UUID_V4', ['id_shop' => (int) $idShop])
        ) {
            return false;
        }

        if (false === $this->shopProvider->isFeatureActive()
            && false === $this->prestaShopConfiguration->has('PS_CHECKOUT_SHOP_UUID_V4')
        ) {
            return false;
        }

        return (bool) $this->getForShop($idShop);
    }
}
