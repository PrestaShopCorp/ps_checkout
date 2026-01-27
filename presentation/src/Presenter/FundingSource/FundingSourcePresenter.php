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

namespace PsCheckout\Presentation\Presenter\FundingSource;

use PsCheckout\Core\FundingSource\Repository\FundingSourceRepositoryInterface;
use PsCheckout\Core\FundingSource\ValueObject\FundingSource;

class FundingSourcePresenter implements FundingSourcePresenterInterface
{
    /**
     * @var string
     */
    private $modulePathUri;

    /**
     * @var FundingSourceRepositoryInterface
     */
    private $fundingSourceRepository;

    /**
     * @var FundingSourceTranslationProviderInterface
     */
    private $fundingSourceTranslationProvider;

    /**
     * @param string $modulePathUri
     * @param FundingSourceRepositoryInterface $fundingSourceRepository
     * @param FundingSourceTranslationProviderInterface $fundingSourceTranslationProvider
     */
    public function __construct(
        string $modulePathUri,
        FundingSourceRepositoryInterface $fundingSourceRepository,
        FundingSourceTranslationProviderInterface $fundingSourceTranslationProvider
    ) {
        $this->modulePathUri = $modulePathUri;
        $this->fundingSourceRepository = $fundingSourceRepository;
        $this->fundingSourceTranslationProvider = $fundingSourceTranslationProvider;
    }

    /**
     * {@inheritdoc}
     */
    public function getOneBy(array $keyValueCriteria)
    {
        $fundingSource = $this->fundingSourceRepository->getOneBy($keyValueCriteria);

        return $fundingSource ? $this->buildFundingSourceObject($fundingSource) : null;
    }

    /**
     * {@inheritdoc}
     */
    public function getAllForSpecificShop(int $shopId): array
    {
        $fundingSourceData = $this->fundingSourceRepository->getAllForSpecificShop($shopId);
        $fundingSources = [];

        foreach ($fundingSourceData as $fundingSource) {
            $fundingSources[] = $this->buildFundingSourceObject($fundingSource);
        }

        return $fundingSources;
    }

    /**
     * {@inheritdoc}
     */
    public function getAllActiveForSpecificShop(int $shopId): array
    {
        $fundingSourceData = $this->fundingSourceRepository->getAllActiveForSpecificShop($shopId);
        $fundingSources = [];

        foreach ($fundingSourceData as $fundingSource) {
            $fundingSources[] = $this->buildFundingSourceObject($fundingSource);
        }

        return $fundingSources;
    }

    /**
     * @param array $fundingSource
     *
     * @return FundingSource
     */
    private function buildFundingSourceObject(array $fundingSource): FundingSource
    {
        return new FundingSource(
            $fundingSource['name'],
            $this->fundingSourceTranslationProvider->getFundingSourceName($fundingSource['name']),
            $fundingSource['position'],
            (bool) $fundingSource['active'],
            in_array($fundingSource['name'], ['google_pay', 'apple_pay']) ? $this->modulePathUri . 'views/img/' . $fundingSource['name'] . '.svg' : null
        );
    }
}
