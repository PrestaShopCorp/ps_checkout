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

namespace PrestaShop\Module\PrestashopCheckout\FundingSource;

use PrestaShop\Module\PrestashopCheckout\Repository\CountryRepository;

class FundingSourcePresenter
{
    /**
     * @var FundingSourceTranslationProvider
     */
    private $translation;

    /**
     * @var CountryRepository
     */
    private $country;

    /**
     * @param FundingSourceTranslationProvider $translation
     * @param CountryRepository $country
     */
    public function __construct(FundingSourceTranslationProvider $translation, CountryRepository $country)
    {
        $this->translation = $translation;
        $this->country = $country;
    }

    /**
     * @param FundingSourceEntity $entity
     * @param bool $isAdmin
     *
     * @return FundingSource
     */
    public function present($entity, $isAdmin)
    {
        $name = $entity->getName();

        return new FundingSource(
            $name,
            $isAdmin ? $this->translation->getPaymentMethodName($name) : $this->translation->getPaymentOptionName($name),
            $entity->getPosition(),
            $isAdmin ? $this->country->getCountryNames($entity->getCountries()) : $entity->getCountries(),
            $entity->getIsEnabled(),
            $entity->getIsToggleable()
        );
    }
}
