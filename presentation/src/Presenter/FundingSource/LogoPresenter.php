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

class LogoPresenter implements LogoPresenterInterface
{
    /**
     * @var string
     */
    private $modulePath;

    public function __construct($modulePath)
    {
        $this->modulePath = $modulePath;
    }

    /**
     * {@inheritdoc}
     */
    public function getLogoByPaymentSource(array $paymentSource): string
    {
        $paymentSourceName = key($paymentSource);

        if ($paymentSourceName === 'card' && isset($paymentSource['card']['brand'])) {
            switch ($paymentSource['card']['brand']) {
                case 'CB_NATIONALE':
                    return $this->modulePath . 'views/img/cb.svg';
                case 'VISA':
                    return $this->modulePath . 'views/img/visa.svg';
                case 'MASTERCARD':
                    return $this->modulePath . 'views/img/mastercard.svg';
                case 'AMEX':
                    return $this->modulePath . 'views/img/amex.svg';
                case 'DISCOVER':
                    return $this->modulePath . 'views/img/discover.svg';
                case 'JCB':
                    return $this->modulePath . 'views/img/jcb.svg';
                case 'DINERS':
                    return $this->modulePath . 'views/img/diners.svg';
                case 'UNIONPAY':
                    return $this->modulePath . 'views/img/unionpay.svg';
                case 'MAESTRO':
                    return $this->modulePath . 'views/img/maestro.svg';
            }
        }

        return $this->modulePath . 'views/img/' . $paymentSourceName . '.svg';
    }
}
