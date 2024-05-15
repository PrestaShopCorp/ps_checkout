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

namespace PrestaShop\Module\PrestashopCheckout\PayPal\Order\DTO;

class PaymentSourceRequest
{
    /**
     * @var CardRequest
     */
    private $card;
    /**
     * @var TokenRequest
     */
    private $token;
    /**
     * @var PayPalRequest
     */
    private $paypal;
    /**
     * @var BancontactRequest
     */
    private $bancontact;
    /**
     * @var BlikRequest
     */
    private $blik;
    /**
     * @var EpsRequest
     */
    private $eps;
    /**
     * @var GiropayRequest
     */
    private $giropay;
    /**
     * @var IdealRequest
     */
    private $ideal;
    /**
     * @var MyBankRequest
     */
    private $mybank;
    /**
     * @var P24Request
     */
    private $p24;
    /**
     * @var SofortRequest
     */
    private $sofort;
    /**
     * @var ApplePayRequest
     */
    private $apple_pay;
    /**
     * @var GooglePayRequest
     */
    private $google_pay;
    /**
     * @var VenmoRequest
     */
    private $venmo;

    /**
     * @return CardRequest
     */
    public function getCard()
    {
        return $this->card;
    }

    /**
     * @param CardRequest $card
     *
     * @return void
     */
    public function setCard(CardRequest $card)
    {
        $this->card = $card;
    }

    /**
     * @return TokenRequest
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * @param TokenRequest $token
     *
     * @return void
     */
    public function setToken(TokenRequest $token)
    {
        $this->token = $token;
    }

    /**
     * @return PayPalRequest
     */
    public function getPaypal()
    {
        return $this->paypal;
    }

    /**
     * @param PayPalRequest $paypal
     *
     * @return void
     */
    public function setPaypal(PayPalRequest $paypal)
    {
        $this->paypal = $paypal;
    }

    /**
     * @return BancontactRequest
     */
    public function getBancontact()
    {
        return $this->bancontact;
    }

    /**
     * @param BancontactRequest $bancontact
     *
     * @return void
     */
    public function setBancontact(BancontactRequest $bancontact)
    {
        $this->bancontact = $bancontact;
    }

    /**
     * @return BlikRequest
     */
    public function getBlik()
    {
        return $this->blik;
    }

    /**
     * @param BlikRequest $blik
     *
     * @return void
     */
    public function setBlik(BlikRequest $blik)
    {
        $this->blik = $blik;
    }

    /**
     * @return EpsRequest
     */
    public function getEps()
    {
        return $this->eps;
    }

    /**
     * @param EpsRequest $eps
     *
     * @return void
     */
    public function setEps(EpsRequest $eps)
    {
        $this->eps = $eps;
    }

    /**
     * @return GiropayRequest
     */
    public function getGiropay()
    {
        return $this->giropay;
    }

    /**
     * @param GiropayRequest $giropay
     *
     * @return void
     */
    public function setGiropay(GiropayRequest $giropay)
    {
        $this->giropay = $giropay;
    }

    /**
     * @return IdealRequest
     */
    public function getIdeal()
    {
        return $this->ideal;
    }

    /**
     * @param IdealRequest $ideal
     *
     * @return void
     */
    public function setIdeal(IdealRequest $ideal)
    {
        $this->ideal = $ideal;
    }

    /**
     * @return MyBankRequest
     */
    public function getMybank()
    {
        return $this->mybank;
    }

    /**
     * @param MyBankRequest $mybank
     *
     * @return void
     */
    public function setMybank(MyBankRequest $mybank)
    {
        $this->mybank = $mybank;
    }

    /**
     * @return P24Request
     */
    public function getP24()
    {
        return $this->p24;
    }

    /**
     * @param P24Request $p24
     *
     * @return void
     */
    public function setP24(P24Request $p24)
    {
        $this->p24 = $p24;
    }

    /**
     * @return SofortRequest
     */
    public function getSofort()
    {
        return $this->sofort;
    }

    /**
     * @param SofortRequest $sofort
     *
     * @return void
     */
    public function setSofort(SofortRequest $sofort)
    {
        $this->sofort = $sofort;
    }

    /**
     * @return ApplePayRequest
     */
    public function getApplePay()
    {
        return $this->apple_pay;
    }

    /**
     * @param ApplePayRequest $apple_pay
     *
     * @return void
     */
    public function setApplePay(ApplePayRequest $apple_pay)
    {
        $this->apple_pay = $apple_pay;
    }

    /**
     * @return GooglePayRequest
     */
    public function getGooglePay()
    {
        return $this->google_pay;
    }

    /**
     * @param GooglePayRequest $google_pay
     *
     * @return void
     */
    public function setGooglePay(GooglePayRequest $google_pay)
    {
        $this->google_pay = $google_pay;
    }

    /**
     * @return VenmoRequest
     */
    public function getVenmo()
    {
        return $this->venmo;
    }

    /**
     * @param VenmoRequest $venmo
     *
     * @return void
     */
    public function setVenmo(VenmoRequest $venmo)
    {
        $this->venmo = $venmo;
    }
}
