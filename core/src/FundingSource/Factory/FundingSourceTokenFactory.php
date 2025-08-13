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

namespace PsCheckout\Core\FundingSource\Factory;

use PsCheckout\Core\FundingSource\ValueObject\FundingSourceToken;
use PsCheckout\Core\PaymentToken\ValueObject\PaymentToken;
use PsCheckout\Presentation\Presenter\FundingSource\FundingSourceTranslationProviderInterface;
use PsCheckout\Presentation\Presenter\FundingSource\LogoPresenterInterface;

class FundingSourceTokenFactory implements FundingSourceTokenFactoryInterface
{
    /**
     * @var FundingSourceTranslationProviderInterface
     */
    private $fundingSourceTranslationProvider;

    /**
     * @var LogoPresenterInterface
     */
    private $logoPresenter;

    public function __construct(
        FundingSourceTranslationProviderInterface $fundingSourceTranslationProvider,
        LogoPresenterInterface $logoPresenter
    ) {
        $this->fundingSourceTranslationProvider = $fundingSourceTranslationProvider;
        $this->logoPresenter = $logoPresenter;
    }

    /**
     * {@inheritdoc}
     */
    public function createFromPaymentToken(PaymentToken $paymentToken): FundingSourceToken
    {
        $paymentSource = $paymentToken->getData()['payment_source'][$paymentToken->getPaymentSource()];
        $label = $this->getLabel($paymentToken, $paymentSource);

        return new FundingSourceToken(
            'token-' . $paymentToken->getId(),
            $label,
            $paymentToken->getPaymentSource(),
            $paymentToken->isFavorite(),
            $this->logoPresenter->getLogoByPaymentSource($paymentToken->getData()['payment_source'])
        );
    }

    /**
     * @param PaymentToken $paymentToken
     * @param array $paymentSource
     *
     * @return string
     */
    private function getLabel(PaymentToken $paymentToken, array $paymentSource): string
    {
        if ($paymentToken->getPaymentSource() === 'card') {
            return $this->fundingSourceTranslationProvider->getVaultedPaymentMethodName(
                (isset($paymentSource['brand']) ? $paymentSource['brand'] : '') .
                (isset($paymentSource['last_digits']) ? ' *' . $paymentSource['last_digits'] : '')
            );
        }

        return $this->fundingSourceTranslationProvider->getVaultedPaymentMethodName(
            isset($paymentSource['email_address']) ? $paymentSource['email_address'] : ''
        );
    }
}
