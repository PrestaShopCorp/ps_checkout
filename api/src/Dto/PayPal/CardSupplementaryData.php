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
 * Merchants and partners can add Level 2 and 3 data to payments to reduce risk and payment processing
 * costs. For more information about processing payments, see checkout or multiparty checkout.
 */
class CardSupplementaryData
{
    /**
     * @var Level2CardProcessingData|null
     */
    private $level2;

    /**
     * @var Level3CardProcessingData|null
     */
    private $level3;

    /**
     * Returns Level 2.
     * The level 2 card processing data collections. If your merchant account has been configured for Level
     * 2 processing this field will be passed to the processor on your behalf. Please contact your PayPal
     * Technical Account Manager to define level 2 data for your business.
     */
    public function getLevel2(): ?Level2CardProcessingData
    {
        return $this->level2;
    }

    /**
     * Sets Level 2.
     * The level 2 card processing data collections. If your merchant account has been configured for Level
     * 2 processing this field will be passed to the processor on your behalf. Please contact your PayPal
     * Technical Account Manager to define level 2 data for your business.
     *
     * @maps level_2
     * @return self
     */
    public function setLevel2(?Level2CardProcessingData $level2): self
    {
        $this->level2 = $level2;

        return $this;
    }

    /**
     * Returns Level 3.
     * The level 3 card processing data collections, If your merchant account has been configured for Level
     * 3 processing this field will be passed to the processor on your behalf. Please contact your PayPal
     * Technical Account Manager to define level 3 data for your business.
     */
    public function getLevel3(): ?Level3CardProcessingData
    {
        return $this->level3;
    }

    /**
     * Sets Level 3.
     * The level 3 card processing data collections, If your merchant account has been configured for Level
     * 3 processing this field will be passed to the processor on your behalf. Please contact your PayPal
     * Technical Account Manager to define level 3 data for your business.
     *
     * @maps level_3
     * @return self
     */
    public function setLevel3(?Level3CardProcessingData $level3): self
    {
        $this->level3 = $level3;

        return $this;
    }
}
