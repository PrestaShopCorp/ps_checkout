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

namespace PsCheckout\Infrastructure\Service;

class CountryResolutionException extends \RuntimeException
{
    const COUNTRY_NOT_FOUND = 1;

    const COUNTRY_NOT_AVAILABLE = 2;

    /**
     * @var string
     */
    private $shopIsoCode;

    /**
     * @var int
     */
    private $idCountry;

    public function __construct(string $message, int $code, string $shopIsoCode, int $idCountry = 0)
    {
        parent::__construct($message, $code);
        $this->shopIsoCode = $shopIsoCode;
        $this->idCountry = $idCountry;
    }

    public function getShopIsoCode(): string
    {
        return $this->shopIsoCode;
    }

    public function getIdCountry(): int
    {
        return $this->idCountry;
    }
}
