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

class PaymentCollection
{
    /**
     * An array of authorized payments for a purchase unit. A purchase unit can have zero or more authorized payments.
     *
     * @var AuthorizationWithAdditionalData[]|null
     */
    protected $authorizations;

    /**
     * An array of captured payments for a purchase unit. A purchase unit can have zero or more captured payments.
     *
     * @var Capture[]|null
     */
    protected $captures;

    /**
     * An array of refunds for a purchase unit. A purchase unit can have zero or more refunds.
     *
     * @var Refund[]|null
     */
    protected $refunds;

    /**
     * Constructor
     *
     * @param mixed[] $data Associated array of property values initializing the model
     */
    public function __construct(array $data = null)
    {
        $this->authorizations = isset($data['authorizations']) ? $data['authorizations'] : null;
        $this->captures = isset($data['captures']) ? $data['captures'] : null;
        $this->refunds = isset($data['refunds']) ? $data['refunds'] : null;
    }

    /**
     * Gets authorizations.
     *
     * @return AuthorizationWithAdditionalData[]|null
     */
    public function getAuthorizations()
    {
        return $this->authorizations;
    }

    /**
     * Sets authorizations.
     *
     * @param AuthorizationWithAdditionalData[]|null $authorizations An array of authorized payments for a purchase unit. A purchase unit can have zero or more authorized payments.
     *
     * @return $this
     */
    public function setAuthorizations(array $authorizations = null)
    {
        $this->authorizations = $authorizations;

        return $this;
    }

    /**
     * Gets captures.
     *
     * @return Capture[]|null
     */
    public function getCaptures()
    {
        return $this->captures;
    }

    /**
     * Sets captures.
     *
     * @param Capture[]|null $captures An array of captured payments for a purchase unit. A purchase unit can have zero or more captured payments.
     *
     * @return $this
     */
    public function setCaptures(array $captures = null)
    {
        $this->captures = $captures;

        return $this;
    }

    /**
     * Gets refunds.
     *
     * @return Refund[]|null
     */
    public function getRefunds()
    {
        return $this->refunds;
    }

    /**
     * Sets refunds.
     *
     * @param Refund[]|null $refunds An array of refunds for a purchase unit. A purchase unit can have zero or more refunds.
     *
     * @return $this
     */
    public function setRefunds(array $refunds = null)
    {
        $this->refunds = $refunds;

        return $this;
    }
}
