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

namespace PsCheckout\Core\PayPal\ShippingTracking\Builder\Node;

class TrackingCarrierModuleNodeBuilder implements TrackingCarrierModuleNodeBuilderInterface
{
    /**
     * @var string
     */
    private $carrierNameOther = '';

    /**
     * @var string
     */
    private $moduleName = '';

    /**
     * @var string
     */
    private $moduleVersion = '';

    /**
     * @var string
     */
    private $deliveryOption = '';

    /**
     * {@inheritDoc}
     */
    public function setCarrierNameOther(string $carrierNameOther): TrackingCarrierModuleNodeBuilderInterface
    {
        $this->carrierNameOther = $carrierNameOther;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function setCarrierModule(string $name, string $version, string $deliveryOption): TrackingCarrierModuleNodeBuilderInterface
    {
        $this->moduleName = $name;
        $this->moduleVersion = $version;
        $this->deliveryOption = $deliveryOption;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function build(): array
    {
        $payload = [];

        // Only add carrier_name_other if it's not empty
        if (!empty($this->carrierNameOther)) {
            $payload['carrier_name_other'] = $this->carrierNameOther;
        }

        // Only add carrier_module if module name is not empty
        if (!empty($this->moduleName)) {
            $payload['carrier_module'] = [
                'name' => $this->moduleName,
                'version' => $this->moduleVersion,
                'delivery_option' => $this->deliveryOption,
            ];
        }

        return $payload;
    }
}
