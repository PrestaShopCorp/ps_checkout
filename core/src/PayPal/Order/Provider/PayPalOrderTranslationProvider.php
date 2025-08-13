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

namespace PsCheckout\Core\PayPal\Order\Provider;

use PsCheckout\Presentation\TranslatorInterface;

class PayPalOrderTranslationProvider implements PayPalOrderTranslationProviderInterface
{
    /**
     * @var array
     */
    private $transactionStatusTranslations;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @param TranslatorInterface $translator
     */
    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;

        $this->initializeTranslations();
    }

    /**
     * {@inheritDoc}
     */
    public function getTransactionStatusTranslated(string $transactionStatus): string
    {
        return $this->transactionStatusTranslations[$transactionStatus] ?? '';
    }

    private function initializeTranslations()
    {
        $this->transactionStatusTranslations = [
            'COMPLETED' => $this->translator->trans('Completed'),
            'DECLINED' => $this->translator->trans('Declined'),
            'PARTIALLY_REFUNDED' => $this->translator->trans('Partially refunded'),
            'PENDING' => $this->translator->trans('Pending'),
            'REFUNDED' => $this->translator->trans('Refunded'),
            'FAILED' => $this->translator->trans('Failed'),
        ];
    }
}
