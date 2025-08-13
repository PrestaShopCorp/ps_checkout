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

namespace PsCheckout\Presentation\Presenter\Date;

use DateTime;
use DateTimeZone;
use PsCheckout\Infrastructure\Adapter\ConfigurationInterface;

class DatePresenter implements DatePresenterInterface
{
    /**
     * @var ConfigurationInterface
     */
    private $configuration;

    /**
     * @param ConfigurationInterface $configuration
     */
    public function __construct(ConfigurationInterface $configuration)
    {
        $this->configuration = $configuration;
    }

    /** {@inheritdoc} */
    public function present(string $timestamp, string $format): string
    {
        $date = new DateTime($timestamp);

        $date->setTimezone($this->getTimeZone());

        return $date->format($format);
    }

    /**
     * @return DateTimeZone
     */
    private function getTimeZone(): DateTimeZone
    {
        $psTimeZone = $this->configuration->get('PS_TIMEZONE');

        if (empty($psTimeZone)) {
            $psTimeZone = date_default_timezone_get();
        }

        return new DateTimeZone($psTimeZone);
    }
}
