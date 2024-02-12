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

class PaymentSourceResponse
{
    /**
     * @var CardResponse|null
     */
    protected $card;

    /**
     * @var PaypalWalletResponse|null
     */
    protected $paypal;

    /**
     * @var Bancontact|null
     */
    protected $bancontact;

    /**
     * @var Blik|null
     */
    protected $blik;

    /**
     * @var Eps|null
     */
    protected $eps;

    /**
     * @var Giropay|null
     */
    protected $giropay;

    /**
     * @var Ideal|null
     */
    protected $ideal;

    /**
     * @var Mybank|null
     */
    protected $mybank;

    /**
     * @var P24|null
     */
    protected $p24;

    /**
     * @var Sofort|null
     */
    protected $sofort;

    /**
     * @var Trustly|null
     */
    protected $trustly;

    /**
     * @var VenmoWalletResponse|null
     */
    protected $venmo;

    /**
     * Constructor
     *
     * @param mixed[] $data Associated array of property values initializing the model
     */
    public function __construct(array $data = null)
    {
        $this->card = isset($data['card']) ? $data['card'] : null;
        $this->paypal = isset($data['paypal']) ? $data['paypal'] : null;
        $this->bancontact = isset($data['bancontact']) ? $data['bancontact'] : null;
        $this->blik = isset($data['blik']) ? $data['blik'] : null;
        $this->eps = isset($data['eps']) ? $data['eps'] : null;
        $this->giropay = isset($data['giropay']) ? $data['giropay'] : null;
        $this->ideal = isset($data['ideal']) ? $data['ideal'] : null;
        $this->mybank = isset($data['mybank']) ? $data['mybank'] : null;
        $this->p24 = isset($data['p24']) ? $data['p24'] : null;
        $this->sofort = isset($data['sofort']) ? $data['sofort'] : null;
        $this->trustly = isset($data['trustly']) ? $data['trustly'] : null;
        $this->venmo = isset($data['venmo']) ? $data['venmo'] : null;
    }

    /**
     * Gets card.
     *
     * @return CardResponse|null
     */
    public function getCard()
    {
        return $this->card;
    }

    /**
     * Sets card.
     *
     * @param CardResponse|null $card
     *
     * @return $this
     */
    public function setCard(CardResponse $card = null)
    {
        $this->card = $card;

        return $this;
    }

    /**
     * Gets paypal.
     *
     * @return PaypalWalletResponse|null
     */
    public function getPaypal()
    {
        return $this->paypal;
    }

    /**
     * Sets paypal.
     *
     * @param PaypalWalletResponse|null $paypal
     *
     * @return $this
     */
    public function setPaypal(PaypalWalletResponse $paypal = null)
    {
        $this->paypal = $paypal;

        return $this;
    }

    /**
     * Gets bancontact.
     *
     * @return Bancontact|null
     */
    public function getBancontact()
    {
        return $this->bancontact;
    }

    /**
     * Sets bancontact.
     *
     * @param Bancontact|null $bancontact
     *
     * @return $this
     */
    public function setBancontact(Bancontact $bancontact = null)
    {
        $this->bancontact = $bancontact;

        return $this;
    }

    /**
     * Gets blik.
     *
     * @return Blik|null
     */
    public function getBlik()
    {
        return $this->blik;
    }

    /**
     * Sets blik.
     *
     * @param Blik|null $blik
     *
     * @return $this
     */
    public function setBlik(Blik $blik = null)
    {
        $this->blik = $blik;

        return $this;
    }

    /**
     * Gets eps.
     *
     * @return Eps|null
     */
    public function getEps()
    {
        return $this->eps;
    }

    /**
     * Sets eps.
     *
     * @param Eps|null $eps
     *
     * @return $this
     */
    public function setEps(Eps $eps = null)
    {
        $this->eps = $eps;

        return $this;
    }

    /**
     * Gets giropay.
     *
     * @return Giropay|null
     */
    public function getGiropay()
    {
        return $this->giropay;
    }

    /**
     * Sets giropay.
     *
     * @param Giropay|null $giropay
     *
     * @return $this
     */
    public function setGiropay(Giropay $giropay = null)
    {
        $this->giropay = $giropay;

        return $this;
    }

    /**
     * Gets ideal.
     *
     * @return Ideal|null
     */
    public function getIdeal()
    {
        return $this->ideal;
    }

    /**
     * Sets ideal.
     *
     * @param Ideal|null $ideal
     *
     * @return $this
     */
    public function setIdeal(Ideal $ideal = null)
    {
        $this->ideal = $ideal;

        return $this;
    }

    /**
     * Gets mybank.
     *
     * @return Mybank|null
     */
    public function getMybank()
    {
        return $this->mybank;
    }

    /**
     * Sets mybank.
     *
     * @param Mybank|null $mybank
     *
     * @return $this
     */
    public function setMybank(Mybank $mybank = null)
    {
        $this->mybank = $mybank;

        return $this;
    }

    /**
     * Gets p24.
     *
     * @return P24|null
     */
    public function getP24()
    {
        return $this->p24;
    }

    /**
     * Sets p24.
     *
     * @param P24|null $p24
     *
     * @return $this
     */
    public function setP24(P24 $p24 = null)
    {
        $this->p24 = $p24;

        return $this;
    }

    /**
     * Gets sofort.
     *
     * @return Sofort|null
     */
    public function getSofort()
    {
        return $this->sofort;
    }

    /**
     * Sets sofort.
     *
     * @param Sofort|null $sofort
     *
     * @return $this
     */
    public function setSofort(Sofort $sofort = null)
    {
        $this->sofort = $sofort;

        return $this;
    }

    /**
     * Gets trustly.
     *
     * @return Trustly|null
     */
    public function getTrustly()
    {
        return $this->trustly;
    }

    /**
     * Sets trustly.
     *
     * @param Trustly|null $trustly
     *
     * @return $this
     */
    public function setTrustly(Trustly $trustly = null)
    {
        $this->trustly = $trustly;

        return $this;
    }

    /**
     * Gets venmo.
     *
     * @return VenmoWalletResponse|null
     */
    public function getVenmo()
    {
        return $this->venmo;
    }

    /**
     * Sets venmo.
     *
     * @param VenmoWalletResponse|null $venmo
     *
     * @return $this
     */
    public function setVenmo(VenmoWalletResponse $venmo = null)
    {
        $this->venmo = $venmo;

        return $this;
    }
}
