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

class NetworkTransactionReference
{
    /**
     * @var string
     */
    private $id;
    /**
     * @var string
     */
    private $date;
    /**
     * @var string
     */
    private $network;
    /**
     * @var string
     */
    private $acquirer_reference_number;

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $id
     *
     * @return void
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * @param string $date
     *
     * @return void
     */
    public function setDate($date)
    {
        $this->date = $date;
    }

    /**
     * @return string
     */
    public function getNetwork()
    {
        return $this->network;
    }

    /**
     * @param string $network
     *
     * @return void
     */
    public function setNetwork($network)
    {
        $this->network = $network;
    }

    /**
     * @return string
     */
    public function getAcquirerReferenceNumber()
    {
        return $this->acquirer_reference_number;
    }

    /**
     * @param string $acquirer_reference_number
     *
     * @return void
     */
    public function setAcquirerReferenceNumber($acquirer_reference_number)
    {
        $this->acquirer_reference_number = $acquirer_reference_number;
    }
}
