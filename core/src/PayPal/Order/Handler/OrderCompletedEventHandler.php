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

namespace PsCheckout\Core\PayPal\Order\Handler;

use PsCheckout\Api\ValueObject\PayPalOrderResponse;
use PsCheckout\Core\Exception\PsCheckoutException;
use PsCheckout\Core\PayPal\Card3DSecure\Card3DSecureConfiguration;
use PsCheckout\Core\PayPal\Card3DSecure\Card3DSecureValidatorInterface;
use PsCheckout\Core\PayPal\Order\Action\UpdatePayPalOrderPurchaseUnitActionInterface;
use PsCheckout\Core\PayPal\Order\Configuration\PayPalOrderStatus;
use PsCheckout\Core\PayPal\Order\Entity\PayPalOrder;
use PsCheckout\Core\PayPal\Order\Repository\PayPalOrderRepositoryInterface;
use PsCheckout\Core\PayPal\OrderStatus\Action\PayPalCheckOrderStatusActionInterface;
use PsCheckout\Core\Settings\Configuration\PayPalConfiguration;
use PsCheckout\Infrastructure\Adapter\ConfigurationInterface;

class OrderCompletedEventHandler implements EventHandlerInterface
{
    /**
     * @var PayPalOrderRepositoryInterface
     */
    private $payPalOrderRepository;

    /**
     * @var PayPalCheckOrderStatusActionInterface
     */
    private $checkPayPalOrderStatusAction;

    /**
     * @var UpdatePayPalOrderPurchaseUnitActionInterface
     */
    private $updatePayPalOrderPurchaseUnit;

    /**
     * @var ConfigurationInterface
     */
    private $configuration;

    /**
     * @var Card3DSecureValidatorInterface
     */
    private $card3DSecureValidator;

    public function __construct(
        PayPalOrderRepositoryInterface $payPalOrderRepository,
        PayPalCheckOrderStatusActionInterface $checkPayPalOrderStatusAction,
        UpdatePayPalOrderPurchaseUnitActionInterface $updatePayPalOrderPurchaseUnit,
        ConfigurationInterface $configuration,
        Card3DSecureValidatorInterface $card3DSecureValidator
    ) {
        $this->payPalOrderRepository = $payPalOrderRepository;
        $this->checkPayPalOrderStatusAction = $checkPayPalOrderStatusAction;
        $this->updatePayPalOrderPurchaseUnit = $updatePayPalOrderPurchaseUnit;
        $this->configuration = $configuration;
        $this->card3DSecureValidator = $card3DSecureValidator;
    }

    /**
     * {@inheritDoc}
     */
    public function handle(PayPalOrderResponse $payPalOrderResponse)
    {
        $payPalOrder = $this->payPalOrderRepository->getOneBy(['id' => $payPalOrderResponse->getId()]);

        if (!$payPalOrder) {
            throw new PsCheckoutException('PayPal order not found.', PsCheckoutException::ORDER_NOT_FOUND);
        }

        if (!$this->checkPayPalOrderStatusAction->execute($payPalOrder->getStatus(), PayPalOrderStatus::COMPLETED)) {
            return;
        }

        $payPalOrder->setStatus($payPalOrderResponse->getStatus());
        $payPalOrder->setIntent($payPalOrderResponse->getIntent() ?? 'CAPTURE');

        if ($payPalOrderResponse->getFundingSource()) {
            $payPalOrder->setFundingSource($payPalOrderResponse->getFundingSource());
        }

        if ($payPalOrderResponse->getPaymentSource()) {
            $payPalOrder->setPaymentSource($payPalOrderResponse->getPaymentSource());
        }

        if (in_array($payPalOrderResponse->getFundingSource(), ['apple_pay', 'google_pay', 'card'], true)
            && $this->configuration->get(PayPalConfiguration::PS_CHECKOUT_HOSTED_FIELDS_CONTINGENCIES) !== 'SCA_ALWAYS'
            && $this->card3DSecureValidator->getAuthorizationDecision($payPalOrderResponse) === Card3DSecureConfiguration::DECISION_NO_DECISION) {
            $payPalOrder->addTag(PayPalOrder::THREE_D_SECURE_NOT_REQUIRED);
        }

        $this->payPalOrderRepository->save($payPalOrder);
        $this->updatePayPalOrderPurchaseUnit->execute($payPalOrderResponse);
    }
}
