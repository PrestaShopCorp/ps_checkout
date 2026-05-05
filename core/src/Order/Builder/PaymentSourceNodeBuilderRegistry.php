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

namespace PsCheckout\Core\Order\Builder;

use PsCheckout\Core\Exception\PsCheckoutException;

class PaymentSourceNodeBuilderRegistry implements PaymentSourceNodeBuilderRegistryInterface
{
    /** @var PaymentSourceNodeBuilderInterface[] */
    private $builders;

    /**
     * @param PaymentSourceNodeBuilderInterface[] $builders
     */
    public function __construct(array $builders)
    {
        $this->builders = $builders;
    }

    /**
     * {@inheritDoc}
     */
    public function findBuilder(string $fundingSource): PaymentSourceNodeBuilderInterface
    {
        foreach ($this->builders as $builder) {
            if ($builder->supports($fundingSource)) {
                return $builder;
            }
        }

        throw new PsCheckoutException(
            sprintf('No payment source builder found for funding source "%s"', $fundingSource),
            PsCheckoutException::PAYPAL_FUNDING_SOURCE_UNKNOWN
        );
    }
}
