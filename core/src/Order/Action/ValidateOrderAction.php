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

namespace PsCheckout\Core\Order\Action;

use Exception;
use Ps_Checkout;
use PsCheckout\Core\Order\Exception\OrderException;
use PsCheckout\Core\Order\ValueObject\ValidateOrderData;
use PsCheckout\Presentation\Presenter\FundingSource\FundingSourceTranslationProviderInterface;

class ValidateOrderAction implements ValidateOrderActionInterface
{
    /**
     * @var FundingSourceTranslationProviderInterface
     */
    private $fundingSourceTranslationProvider;

    /**
     * @var Ps_Checkout
     */
    private $module;

    public function __construct(
        FundingSourceTranslationProviderInterface $fundingSourceTranslationProvider,
        Ps_Checkout $module
    ) {
        $this->fundingSourceTranslationProvider = $fundingSourceTranslationProvider;
        $this->module = $module;
    }

    /**
     * {@inheritDoc}
     */
    public function execute(ValidateOrderData $orderData)
    {
        try {
            $this->module->validateOrder(
                (int) $orderData->getCartId(),
                $orderData->getOrderStateId(),
                $orderData->getPaidAmount(),
                $this->fundingSourceTranslationProvider->getFundingSourceName($orderData->getFundingSource()),
                null,
                $orderData->getExtraVars(),
                $orderData->getCurrencyId(),
                false,
                $orderData->getSecureKey()
            );
        } catch (Exception $exception) {
            throw new OrderException(sprintf('Failed to create order from Cart #%s.', var_export($orderData->getCartId(), true)), OrderException::FAILED_ADD_ORDER, $exception);
        }
    }
}
