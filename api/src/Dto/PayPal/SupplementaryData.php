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
 * Supplementary data about a payment. This object passes information that can be used to improve risk
 * assessments and processing costs, for example, by providing Level 2 and Level 3 payment data.
 */
class SupplementaryData
{
    /**
     * @var CardSupplementaryData|null
     */
    private $card;

    /**
     * @var RiskSupplementaryData|null
     */
    private $risk;

    /**
     * Returns Card.
     * Merchants and partners can add Level 2 and 3 data to payments to reduce risk and payment processing
     * costs. For more information about processing payments, see checkout or multiparty checkout.
     */
    public function getCard(): ?CardSupplementaryData
    {
        return $this->card;
    }

    /**
     * Sets Card.
     * Merchants and partners can add Level 2 and 3 data to payments to reduce risk and payment processing
     * costs. For more information about processing payments, see checkout or multiparty checkout.
     *
     * @maps card
     * @return self
     */
    public function setCard(?CardSupplementaryData $card): self
    {
        $this->card = $card;

        return $this;
    }

    /**
     * Returns Risk.
     * Additional information necessary to evaluate the risk profile of a transaction.
     */
    public function getRisk(): ?RiskSupplementaryData
    {
        return $this->risk;
    }

    /**
     * Sets Risk.
     * Additional information necessary to evaluate the risk profile of a transaction.
     *
     * @maps risk
     * @return self
     */
    public function setRisk(?RiskSupplementaryData $risk): self
    {
        $this->risk = $risk;

        return $this;
    }
}
