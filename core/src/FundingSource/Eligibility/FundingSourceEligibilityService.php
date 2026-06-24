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

namespace PsCheckout\Core\FundingSource\Eligibility;

use PsCheckout\Core\FundingSource\Eligibility\Checker\FundingSourceEligibilityCheckerInterface;
use PsCheckout\Core\FundingSource\ValueObject\FundingSource;
use PsCheckout\Infrastructure\Adapter\ContextInterface;
use PsCheckout\Presentation\Presenter\FundingSource\FundingSourcePresenterInterface;

class FundingSourceEligibilityService implements FundingSourceEligibilityServiceInterface
{
    /** @var ContextInterface */
    private $context;

    /** @var FundingSourcePresenterInterface */
    private $fundingSourcePresenter;

    /** @var iterable<FundingSourceEligibilityCheckerInterface> */
    private $checkers;

    /**
     * @param FundingSourceEligibilityCheckerInterface[] $checkers
     */
    public function __construct(
        ContextInterface $context,
        FundingSourcePresenterInterface $fundingSourcePresenter,
        iterable $checkers
    ) {
        $this->context = $context;
        $this->fundingSourcePresenter = $fundingSourcePresenter;
        $this->checkers = $checkers;
    }

    /**
     * {@inheritdoc}
     */
    public function getEligibleFundingSources(): array
    {
        $eligible = [];

        $shop = $this->context->getShop();
        if (null === $shop || !$shop->id) {
            return $eligible;
        }

        $fundingSources = $this->fundingSourcePresenter->getAllActiveForSpecificShop($shop->id);

        foreach ($fundingSources as $fundingSource) {
            $name = $fundingSource->getName();
            if ($fundingSource->getIsEnabled() && $this->isFundingSourceEligible($fundingSource)) {
                $eligible[$name] = $fundingSource;
            }
        }

        return $eligible;
    }

    /**
     * {@inheritdoc}
     */
    public function isFundingSourceEligible(FundingSource $fundingSource): bool
    {
        foreach ($this->checkers as $checker) {
            if ($checker instanceof FundingSourceEligibilityCheckerInterface && $checker->supports($fundingSource)) {
                return $checker->isEligible($fundingSource);
            }
        }

        return true;
    }
}
