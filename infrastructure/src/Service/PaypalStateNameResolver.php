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

use PsCheckout\Infrastructure\Repository\StateRepositoryInterface;
use PsCheckout\Utility\Payload\PaypalAddressRequirementsUtility;
use PsCheckout\Utility\Payload\PaypalStateCodeMapUtility;

class PaypalStateNameResolver
{
    /**
     * @var StateRepositoryInterface
     */
    private $stateRepository;

    public function __construct(StateRepositoryInterface $stateRepository)
    {
        $this->stateRepository = $stateRepository;
    }

    /**
     * Resolve a PrestaShop state ID to a PayPal-compatible state name or ISO code.
     *
     * Applies country-specific ISO vs. name preference and the PayPal state code mapping.
     */
    public function resolve(string $countryIso, int $idState): string
    {
        $stateName = PaypalAddressRequirementsUtility::usesStateIsoCode($countryIso)
            ? $this->stateRepository->getIsoById($idState)
            : $this->stateRepository->getNameById($idState);

        return PaypalStateCodeMapUtility::getPaypalStateCode($countryIso, $stateName);
    }
}
