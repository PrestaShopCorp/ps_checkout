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

namespace PsCheckout\Api\Dto\PayPal;

/**
 * Merchant provided, buyer's native app preferences to app switch to the PayPal consumer app.
 */
class NativeAppContext
{
    /**
     * @var string|null
     */
    private $osType;

    /**
     * @var string|null
     */
    private $osVersion;

    /**
     * Returns Os Type.
     * Operating System type of the device that the buyer is using.
     */
    public function getOsType(): ?string
    {
        return $this->osType;
    }

    /**
     * Sets Os Type.
     * Operating System type of the device that the buyer is using.
     *
     * @maps os_type
     * @return self
     */
    public function setOsType(?string $osType): self
    {
        $this->osType = $osType;

        return $this;
    }

    /**
     * Returns Os Version.
     * Operating System version of the device that the buyer is using.
     */
    public function getOsVersion(): ?string
    {
        return $this->osVersion;
    }

    /**
     * Sets Os Version.
     * Operating System version of the device that the buyer is using.
     *
     * @maps os_version
     * @return self
     */
    public function setOsVersion(?string $osVersion): self
    {
        $this->osVersion = $osVersion;

        return $this;
    }
}
