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

use PsCheckout\Api\ValueObject\PayPalOrderResponse;
use PsCheckout\Core\Order\Validator\OrderAmountValidator;
use PsCheckout\Core\Order\Validator\OrderAmountValidatorInterface;
use PsCheckout\Core\Order\ValueObject\ValidateOrderData;
use PsCheckout\Core\OrderState\Configuration\OrderStateConfiguration;
use PsCheckout\Core\OrderState\OrderStateException;
use PsCheckout\Core\OrderState\Service\OrderStateMapperInterface;
use PsCheckout\Infrastructure\Adapter\ContextInterface;
use PsCheckout\Infrastructure\Repository\CurrencyRepositoryInterface;

class CreateValidateOrderDataAction implements CreateValidateOrderDataActionInterface
{
    /**
     * @var ContextInterface
     */
    private $context;

    /**
     * @var OrderStateMapperInterface
     */
    private $orderStateMapper;

    /**
     * @var CurrencyRepositoryInterface
     */
    private $currencyRepository;

    /**
     * @var OrderAmountValidatorInterface
     */
    private $orderAmountValidator;

    public function __construct(
        ContextInterface $context,
        OrderStateMapperInterface $orderStateMapper,
        CurrencyRepositoryInterface $currencyRepository,
        OrderAmountValidatorInterface $orderAmountValidator
    ) {
        $this->context = $context;
        $this->orderStateMapper = $orderStateMapper;
        $this->currencyRepository = $currencyRepository;
        $this->orderAmountValidator = $orderAmountValidator;
    }

    /**
     * {@inheritDoc}
     */
    public function execute(PayPalOrderResponse $paypalOrder): ValidateOrderData
    {
        $fundingSource = $paypalOrder->getFundingSource();
        $cart = $this->context->getCart();

        $paidAmount = '';
        $transactionId = '';
        $orderStateId = '';

        $currencyId = (int) $this->context->getCart()->id_currency;

        $capture = $paypalOrder->getCapture();

        if ($capture) {
            $transactionId = $capture['id'];
            $paidAmount = $capture['status'] === 'COMPLETED' ? $capture['amount']['value'] : '';
            $currencyId = $this->currencyRepository->getIdByIsoCode($capture['amount']['currency_code'], (int) $this->context->getCart()->id_shop);
        }

        try {
            if ($paidAmount) {
                switch ($this->orderAmountValidator->validate((string) $paidAmount, (string) $this->context->getCart()->getOrderTotal(true, \Cart::BOTH))) {
                    case OrderAmountValidator::ORDER_NOT_FULL_PAID:
                        $orderStateId = $this->orderStateMapper->getIdByKey(OrderStateConfiguration::PS_CHECKOUT_STATE_PARTIALLY_PAID);

                        break;
                    case OrderAmountValidator::ORDER_FULL_PAID:
                        $orderStateId = $this->orderStateMapper->getIdByKey(OrderStateConfiguration::PS_CHECKOUT_STATE_COMPLETED);

                        break;
                    case OrderAmountValidator::ORDER_TO_MUCH_PAID:
                        $orderStateId = $this->orderStateMapper->getIdByKey(OrderStateConfiguration::PS_CHECKOUT_STATE_COMPLETED);
                }
            } else {
                $orderStateId = $this->orderStateMapper->getIdByKey(OrderStateConfiguration::PS_CHECKOUT_STATE_PENDING);
            }
        } catch (OrderStateException $exception) {
            $orderStateId = $this->orderStateMapper->getIdByKey(OrderStateConfiguration::PS_CHECKOUT_STATE_PENDING);
        }

        $extraVars = [];

        // Transaction identifier is needed only when an OrderPayment will be created
        // It requires a positive paid amount and an OrderState that's consider the associated order as validated.
        if ($paidAmount && $transactionId) {
            $extraVars['transaction_id'] = $transactionId;
        }

        return ValidateOrderData::create(
            $cart->id,
            $orderStateId,
            (float) $paidAmount,
            $extraVars,
            $currencyId,
            $cart->secure_key,
            $fundingSource
        );
    }
}
