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

namespace PrestaShop\Module\PrestashopCheckout\MarketPlace;

use DateTimeImmutable;

class GetMarketPlaceModuleDataQueryResult
{
    /**
     * @var int
     */
    private $moduleId;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $version;

    /**
     * @var DateTimeImmutable
     */
    private $releasedAt;

    /**
     * @var array
     */
    private $changeLog;

    /**
     * @param int $moduleId
     * @param string $name
     * @param string $version
     * @param string $releasedAt
     * @param array $changeLog
     */
    public function __construct($moduleId, $name, $version, $releasedAt, array $changeLog)
    {
        $this->moduleId = $moduleId;
        $this->name = $name;
        $this->version = $version;
        $this->releasedAt = new DateTimeImmutable($releasedAt);
        $this->changeLog = $changeLog;
    }

    /**
     * @return int
     */
    public function getModuleId()
    {
        return $this->moduleId;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * @return DateTimeImmutable
     */
    public function getReleasedAt()
    {
        return $this->releasedAt;
    }

    /**
     * @return array
     */
    public function getChangeLog()
    {
        return $this->changeLog;
    }
}
