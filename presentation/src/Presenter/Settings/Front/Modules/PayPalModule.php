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

namespace PsCheckout\Presentation\Presenter\Settings\Front\Modules;

use PsCheckout\Core\PayPal\Order\Repository\PayPalOrderRepositoryInterface;
use PsCheckout\Core\Settings\Configuration\PayPalCardConfiguration;
use PsCheckout\Infrastructure\Adapter\ContextInterface;
use PsCheckout\Infrastructure\Environment\EnvInterface;
use PsCheckout\Presentation\Presenter\FundingSource\FundingSourcePresenterInterface;
use PsCheckout\Presentation\Presenter\FundingSource\FundingSourceTokenPresenterInterface;
use PsCheckout\Presentation\Presenter\FundingSource\FundingSourceTranslationProviderInterface;
use PsCheckout\Presentation\Presenter\PresenterInterface;

class PayPalModule implements PresenterInterface
{
    /**
     * @var string
     */
    private $moduleName;

    /**
     * @var string
     */
    private $moduleVersion;

    /**
     * @var ContextInterface
     */
    private $context;

    /**
     * @var EnvInterface
     */
    private $env;

    /**
     * @var FundingSourcePresenterInterface
     */
    private $fundingSourcePresenter;

    /**
     * @var FundingSourcePresenterInterface
     */
    private $fundingSourceTokenPresenter;

    /**
     * @var PresenterInterface
     */
    private $supportedCardBrandsPresenter;

    /**
     * @var PayPalOrderRepositoryInterface
     */
    private $payPalOrderRepository;

    /**
     * @var FundingSourceTranslationProviderInterface
     */
    private $fundingSourceTranslationProvider;

    /**
     * @param string $moduleName
     * @param string $moduleVersion
     * @param ContextInterface $context,
     * @param EnvInterface $env
     * @param FundingSourcePresenterInterface $fundingSourcePresenter
     * @param FundingSourceTokenPresenterInterface $fundingSourceTokenPresenter
     * @param PresenterInterface $supportedCardBrandsPresenter
     * @param PayPalOrderRepositoryInterface $payPalOrderRepository
     * @param FundingSourceTranslationProviderInterface $fundingSourceTranslationProvider
     */
    public function __construct(
        string $moduleName,
        string $moduleVersion,
        ContextInterface $context,
        EnvInterface $env,
        FundingSourcePresenterInterface $fundingSourcePresenter,
        FundingSourceTokenPresenterInterface $fundingSourceTokenPresenter,
        PresenterInterface $supportedCardBrandsPresenter,
        PayPalOrderRepositoryInterface $payPalOrderRepository,
        FundingSourceTranslationProviderInterface $fundingSourceTranslationProvider
    ) {
        $this->moduleName = $moduleName;
        $this->moduleVersion = $moduleVersion;
        $this->context = $context;
        $this->env = $env;
        $this->fundingSourcePresenter = $fundingSourcePresenter;
        $this->fundingSourceTokenPresenter = $fundingSourceTokenPresenter;
        $this->supportedCardBrandsPresenter = $supportedCardBrandsPresenter;
        $this->payPalOrderRepository = $payPalOrderRepository;
        $this->fundingSourceTranslationProvider = $fundingSourceTranslationProvider;
    }

    public function present(): array
    {
        $fundingSourcesSorted = [];
        $payWithTranslations = [];
        $customMarks = [];

        foreach ($this->fundingSourceTokenPresenter->getFundingSourceTokens($this->context->getCustomer()->id ?? 0) as $fundingSource) {
            $fundingSourcesSorted[] = $fundingSource->getName();
            $payWithTranslations[$fundingSource->getName()] = $fundingSource->getLabel();
            $customMarks[$fundingSource->getName()] = $fundingSource->getCustomMark();
        }

        foreach ($this->fundingSourcePresenter->getAllActiveForSpecificShop($this->context->getShop()->id) as $fundingSource) {
            $fundingSourcesSorted[] = $fundingSource->getName();
            $payWithTranslations[$fundingSource->getName()] = $this->fundingSourceTranslationProvider->getPaymentMethodName(
                $fundingSource->getName(),
                $fundingSource->getLabel()
            );

            if ($fundingSource->getCustomMark()) {
                $customMarks[$fundingSource->getName()] = $fundingSource->getCustomMark();
            }
        }

        if (isset($this->context->getCart()->id)) {
            $payPalOrder = $this->payPalOrderRepository->getOneByCartId($this->context->getCart()->id);
        }

        return [
            $this->moduleName . 'Version' => $this->moduleVersion,
            $this->moduleName . 'CustomMarks' => $customMarks,
            $this->moduleName . 'CardBrands' => $this->supportedCardBrandsPresenter->present() ?: PayPalCardConfiguration::DEFAULT_SUPPORTED_CARDS,
            $this->moduleName . 'PayPalOrderId' => isset($payPalOrder) ? $payPalOrder->getId() : '',
            $this->moduleName . 'FundingSource' => isset($payPalOrder) ? $payPalOrder->getFundingSource() : 'paypal',
            $this->moduleName . 'ExpressCheckoutSelected' => isset($payPalOrder) && $payPalOrder->isExpressCheckout(),
            $this->moduleName . 'PartnerAttributionId' => $this->env->getBnCode(),
            $this->moduleName . 'CartProductCount' => $this->context->getCart()->nbProducts(),
            $this->moduleName . 'FundingSourcesSorted' => $fundingSourcesSorted,
            $this->moduleName . 'PayWithTranslations' => $payWithTranslations,
        ];
    }
}
