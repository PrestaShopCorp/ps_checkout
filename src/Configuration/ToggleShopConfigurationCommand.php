<?php

namespace PrestaShop\Module\PrestashopCheckout\Configuration;

class ToggleShopConfigurationCommand
{
    /**
     * @var int
     */
    private $defaultShopId;

    /**
     * @var bool
     */
    private $multiShopFeatureIsCurrentlyEnabled;

    /**
     * @param int $defaultShopId
     * @param bool $multiShopFeatureIsCurrentlyEnabled
     */
    public function __construct($defaultShopId, $multiShopFeatureIsCurrentlyEnabled)
    {
        $this->defaultShopId = $defaultShopId;
        $this->multiShopFeatureIsCurrentlyEnabled = $multiShopFeatureIsCurrentlyEnabled;
    }

    /**
     * @return int
     */
    public function getDefaultShopId()
    {
        return $this->defaultShopId;
    }

    /**
     * @return bool
     */
    public function getMultiShopFeatureIsCurrentlyEnabled()
    {
        return $this->multiShopFeatureIsCurrentlyEnabled;
    }
}
