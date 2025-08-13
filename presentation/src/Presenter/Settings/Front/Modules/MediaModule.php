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

use PsCheckout\Presentation\Presenter\PresenterInterface;

class MediaModule implements PresenterInterface
{
    /**
     * @var string
     */
    private $moduleName;

    /**
     * @var string
     */
    private $modulePathUri;

    /**
     * @param string $moduleName
     * @param string $modulePathUri
     */
    public function __construct(
        string $moduleName,
        string $modulePathUri
    ) {
        $this->moduleName = $moduleName;
        $this->modulePathUri = $modulePathUri;
    }

    public function present(): array
    {
        return [
            $this->moduleName . 'CardLogos' => [
                'AMEX' => $this->modulePathUri . 'views/img/amex.svg',
                'CB_NATIONALE' => $this->modulePathUri . 'views/img/cb.svg',
                'DINERS' => $this->modulePathUri . 'views/img/diners.svg',
                'DISCOVER' => $this->modulePathUri . 'views/img/discover.svg',
                'JCB' => $this->modulePathUri . 'views/img/jcb.svg',
                'MAESTRO' => $this->modulePathUri . 'views/img/maestro.svg',
                'MASTERCARD' => $this->modulePathUri . 'views/img/mastercard.svg',
                'UNIONPAY' => $this->modulePathUri . 'views/img/unionpay.svg',
                'VISA' => $this->modulePathUri . 'views/img/visa.svg',
            ],
            $this->moduleName . 'LoaderImage' => $this->modulePathUri . 'views/img/loader.svg',
            $this->moduleName . 'CardFundingSourceImg' => $this->modulePathUri . 'views/img/payment-cards.png',
            $this->moduleName . 'PaymentMethodLogosTitleImg' => $this->modulePathUri . 'views/img/icons/lock_checkout.svg',
            $this->moduleName . 'IconsPath' => $this->modulePathUri . 'views/img/icons/',
        ];
    }
}
