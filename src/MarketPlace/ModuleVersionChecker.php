<?php

namespace PrestaShop\Module\PrestashopCheckout\MarketPlace;

class ModuleVersionChecker
{
    /**
     * Last available version on PrestaShop Addons for PrestaShop Checkout
     *
     * @var string
     */
    public $lastAvalaibleVersion;

    /**
     * Installed module version
     *
     * @var string
     */
    private $installedVersion;

    /**
     * @param \PrestaShop\Module\PrestashopCheckout\MarketPlace\GetMarketPlaceModuleDataQueryHandler $marketplaceHandler
     * @param \PrestaShop\Module\PrestashopCheckout\MarketPlace\GetMarketPlaceModuleDataQuery $marketplaceQuery
     * @param \Ps_checkout $module
     */
    public function __construct(
        GetMarketPlaceModuleDataQueryHandler $marketplaceHandler,
        GetMarketPlaceModuleDataQuery $marketplaceQuery,
        \Ps_checkout $module
    ) {
        $this->lastAvalaibleVersion = $marketplaceHandler->handle($marketplaceQuery)->getVersion();
        $this->installedVersion = $module->version;
    }

    /**
     * Upgrade version available for PrestaShop Checkout
     *
     * @return bool
     */
    public function upgradeVersionAvailable()
    {
        return version_compare($this->lastAvalaibleVersion, $this->installedVersion, '>');
    }

    /**
     * New major version available on PrestaShop Addons for PrestaShop Checkout
     *
     * @return bool
     */
    public function hasNewMajorVersionAvailable()
    {
        return $this->upgradeVersionAvailable() &&
            $this->compareSpecificVersion('major', $this->lastAvalaibleVersion, $this->installedVersion);
    }

    /**
     * New minor version available on PrestaShop Addons for PrestaShop Checkout
     *
     * @return bool
     */
    public function hasNewMinorVersionAvailable()
    {
        return $this->upgradeVersionAvailable() &&
            !$this->hasNewMajorVersionAvailable() &&
            $this->compareSpecificVersion('minor', $this->lastAvalaibleVersion, $this->installedVersion);
    }

    /**
     * New patch version available on PrestaShop Addons for PrestaShop Checkout
     *
     * @return bool
     */
    public function hasNewPatchVersionAvailable()
    {
        return $this->upgradeVersionAvailable() &&
            !$this->hasNewMinorVersionAvailable() &&
            $this->compareSpecificVersion('patch', $this->lastAvalaibleVersion, $this->installedVersion);
    }

    /**
     * Explode version number for a given version
     *
     * @param string $version
     *
     * @return string
     */
    private function explodeVersion($version)
    {
        $versionArray = explode('.', $version);

        return [
            'major' => $versionArray[0],
            'minor' => $versionArray[1],
            'patch' => $versionArray[2],
        ];
    }

    /**
     * Compare specific version number for given versions (major|minor|patch)
     *
     * @param string $level major|minor|patch
     * @param string $version1
     * @param string $version2
     *
     * @return string
     */
    private function compareSpecificVersion($level, $version1, $version2)
    {
        return version_compare($this->explodeVersion($version1)[$level], $this->explodeVersion($version2)[$level], '>');
    }
}
