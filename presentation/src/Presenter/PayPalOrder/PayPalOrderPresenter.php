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

namespace PsCheckout\Presentation\Presenter\PayPalOrder;

use PsCheckout\Api\ValueObject\PayPalOrderResponse;
use PsCheckout\Core\PayPal\Card3DSecure\Card3DSecureValidatorInterface;
use PsCheckout\Core\PayPal\Order\Configuration\PayPalOrderStatus;
use PsCheckout\Core\PayPal\Order\Entity\PayPalOrder;
use PsCheckout\Core\PayPal\Order\Repository\PayPalOrderRepositoryInterface;
use PsCheckout\Presentation\Presenter\FundingSource\FundingSourceTranslationProviderInterface;
use PsCheckout\Presentation\Presenter\FundingSource\LogoPresenterInterface;
use PsCheckout\Presentation\TranslatorInterface;

class PayPalOrderPresenter implements PayPalOrderPresenterInterface
{
    /**
     * @var PayPalOrderPresenterInterface
     */
    private $payPalOrderTransactionPresenter;

    /**
     * @var PayPalOrderPresenterInterface
     */
    private $payPalOrderTotalsPresenter;

    /**
     * @var Card3DSecureValidatorInterface
     */
    private $card3DSecureValidator;

    /**
     * @var LogoPresenterInterface
     */
    private $logoPresenter;

    /**
     * @var FundingSourceTranslationProviderInterface
     */
    private $fundingSourceTranslationProvider;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var PayPalOrderRepositoryInterface
     */
    private $payPalOrderRepository;

    /**
     * @param PayPalOrderPresenterInterface $payPalOrderTransactionPresenter
     * @param PayPalOrderPresenterInterface $payPalOrderTotalsPresenter
     * @param Card3DSecureValidatorInterface $card3DSecureValidator
     * @param LogoPresenterInterface $logoPresenter
     * @param FundingSourceTranslationProviderInterface $fundingSourceTranslationProvider
     * @param TranslatorInterface $translator
     * @param PayPalOrderRepositoryInterface $payPalOrderRepository
     */
    public function __construct(
        PayPalOrderPresenterInterface $payPalOrderTransactionPresenter,
        PayPalOrderPresenterInterface $payPalOrderTotalsPresenter,
        Card3DSecureValidatorInterface $card3DSecureValidator,
        LogoPresenterInterface $logoPresenter,
        FundingSourceTranslationProviderInterface $fundingSourceTranslationProvider,
        TranslatorInterface $translator,
        PayPalOrderRepositoryInterface $payPalOrderRepository
    ) {
        $this->payPalOrderTransactionPresenter = $payPalOrderTransactionPresenter;
        $this->payPalOrderTotalsPresenter = $payPalOrderTotalsPresenter;
        $this->card3DSecureValidator = $card3DSecureValidator;
        $this->logoPresenter = $logoPresenter;
        $this->fundingSourceTranslationProvider = $fundingSourceTranslationProvider;
        $this->translator = $translator;
        $this->payPalOrderRepository = $payPalOrderRepository;
    }

    /** {@inheritdoc} */
    public function present(PaypalOrderResponse $paypalOrderResponse): array
    {
        $payPalOrder = $this->payPalOrderRepository->getOneBy(['id' => $paypalOrderResponse->getId()]);
        $threeDSNotRequired = $payPalOrder && in_array(PayPalOrder::THREE_D_SECURE_NOT_REQUIRED, $payPalOrder->getTags());

        return array_merge(
            [
                'id' => $paypalOrderResponse->getId(),
                'intent' => $paypalOrderResponse->getIntent(),
                'status' => $this->getOrderStatus($paypalOrderResponse),
                'is3DSNotRequired' => $threeDSNotRequired,
                'is3DSecureAvailable' => $this->card3DSecureValidator->is3DSecureAvailable($paypalOrderResponse),
                'isLiabilityShifted' => $this->card3DSecureValidator->isLiabilityShifted($paypalOrderResponse),
                'paymentSourceName' => $this->fundingSourceTranslationProvider->getFundingSourceName(key($paypalOrderResponse->getPaymentSource())),
                'paymentSourceLogo' => $this->logoPresenter->getLogoByPaymentSource($paypalOrderResponse->getPaymentSource()),
            ],
            $this->payPalOrderTransactionPresenter->present($paypalOrderResponse),
            $this->payPalOrderTotalsPresenter->present($paypalOrderResponse)
        );
    }

    /**
     * @param PaypalOrderResponse $paypalOrderResponse
     *
     * @return array
     */
    private function getOrderStatus(PaypalOrderResponse $paypalOrderResponse): array
    {
        switch ($paypalOrderResponse->getStatus()) {
            case PayPalOrderStatus::CREATED:
                $translated = $this->translator->trans('Created');
                $class = 'info';

                break;
            case PayPalOrderStatus::SAVED:
                $translated = $this->translator->trans('Saved');
                $class = 'info';

                break;
            case PayPalOrderStatus::APPROVED:
                $translated = $this->translator->trans('Approved');
                $class = 'info';

                break;
            case PayPalOrderStatus::VOIDED:
                $translated = $this->translator->trans('Voided');
                $class = 'warning';

                break;
            case PayPalOrderStatus::COMPLETED:
                $translated = $this->translator->trans('Completed');
                $class = 'success';

                break;
            default:
                $translated = '';
                $class = '';
        }

        return [
            'value' => $paypalOrderResponse->getStatus(),
            'translated' => $translated,
            'class' => $class,
        ];
    }
}
