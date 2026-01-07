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

namespace PsCheckout\Api\Dto\PayPal;

/**
 * Profile information of the sender or receiver.
 */
class ParticipantMetadata
{
    /**
     * @var string|null
     */
    private $ipAddress;

    /**
     * Returns Ip Address.
     * An Internet Protocol address (IP address). This address assigns a numerical label to each device
     * that is connected to a computer network through the Internet Protocol. Supports IPv4 and IPv6
     * addresses.
     */
    public function getIpAddress(): ?string
    {
        return $this->ipAddress;
    }

    /**
     * Sets Ip Address.
     * An Internet Protocol address (IP address). This address assigns a numerical label to each device
     * that is connected to a computer network through the Internet Protocol. Supports IPv4 and IPv6
     * addresses.
     *
     * @maps ip_address
     * @return self
     */
    public function setIpAddress(?string $ipAddress): self
    {
        $this->ipAddress = $ipAddress;

        return $this;
    }
}
