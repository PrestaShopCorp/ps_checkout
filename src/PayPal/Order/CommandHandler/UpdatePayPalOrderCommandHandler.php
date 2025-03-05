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

namespace PrestaShop\Module\PrestashopCheckout\PayPal\Order\CommandHandler;

use PrestaShop\Module\PrestashopCheckout\Builder\Payload\OrderPayloadBuilder;
use PrestaShop\Module\PrestashopCheckout\Cart\Exception\CartException;
use PrestaShop\Module\PrestashopCheckout\Exception\PayPalException;
use PrestaShop\Module\PrestashopCheckout\Exception\PsCheckoutException;
use PrestaShop\Module\PrestashopCheckout\Http\MaaslandHttpClient;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\Command\UpdatePayPalOrderCommand;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\Event\PayPalOrderUpdatedEvent;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\EventSubscriber\PayPalOrderEventSubscriber;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\Exception\PayPalOrderException;
use PrestaShop\Module\PrestashopCheckout\PayPal\PayPalOrderProvider;
use PrestaShop\Module\PrestashopCheckout\Presenter\Cart\CartPresenter;

class UpdatePayPalOrderCommandHandler
{
    public function __construct(
        private MaaslandHttpClient $httpClient,
        private PayPalOrderProvider $paypalOrderProvider,
        private PayPalOrderEventSubscriber $payPalOrderEventSubscriber,
    ) {
    }

    /**
     * @param UpdatePayPalOrderCommand $command
     *
     * @return void
     *
     * @throws CartException|PayPalException|PayPalOrderException|PsCheckoutException|\Exception
     */
    public function __invoke(UpdatePayPalOrderCommand $command)
    {
        try {
            $paypalOrder = $this->paypalOrderProvider->getById($command->getPayPalOrderId()->getValue());
        } catch (\Exception $exception) {
            return;
        }

        if (empty($paypalOrder) || empty($paypalOrder['purchase_units'])) {
            return;
        }

        $cartPresenter = (new CartPresenter())->present();
        $builder = new OrderPayloadBuilder($cartPresenter);
        $builder->setIsUpdate(true);
        $builder->setPaypalOrderId($command->getPayPalOrderId()->getValue());
        $builder->setIsCard($command->getFundingSource() === 'card' && $command->isHostedFields());
        $builder->setExpressCheckout($command->isExpressCheckout());

        $builder->buildFullPayload();

        $payload = $builder->presentPayload()->getArray();
        $needToUpdate = false;
        $updatedPayPalOrder = $paypalOrder;

        if (isset($paypalOrder['purchase_units'][0]['amount']) && isset($payload['amount'])) {
            $amountDiff = $this->arrayRecursiveDiff($paypalOrder['purchase_units'][0]['amount'], $payload['amount']);
            if (!empty($amountDiff)) {
                $needToUpdate = true;
                $updatedPayPalOrder['purchase_units'][0]['amount'] = $payload['amount'];
            }
        }

        if (isset($paypalOrder['purchase_units'][0]['items']) && isset($payload['items'])) {
            $itemsDiff = $this->arrayRecursiveDiff($paypalOrder['purchase_units'][0]['items'], $payload['items']);
            if (!empty($itemsDiff)) {
                $needToUpdate = true;
                $updatedPayPalOrder['purchase_units'][0]['items'] = $payload['items'];
            }
        }

        if (isset($paypalOrder['purchase_units'][0]['shipping']) && isset($payload['shipping'])) {
            $shippingDiff = $this->arrayRecursiveDiff($paypalOrder['purchase_units'][0]['shipping'], $payload['shipping']);
            if (!empty($shippingDiff)) {
                $needToUpdate = true;
                $updatedPayPalOrder['purchase_units'][0]['shipping'] = $payload['shipping'];
            }
        }

        if (!$needToUpdate) {
            return;
        }

        $response = $this->httpClient->updateOrder($payload);

        if ($response->getStatusCode() !== 204) {
            throw new PayPalOrderException('Failed to update PayPal Order', PayPalOrderException::PAYPAL_ORDER_UPDATE_FAILED);
        }

        $event = new PayPalOrderUpdatedEvent(
            $command->getPayPalOrderId()->getValue(),
            $updatedPayPalOrder,
            $command->getCartId()->getValue(),
            $command->isHostedFields(),
            $command->isExpressCheckout(),
            $command->getFundingSource()
        );

        $this->payPalOrderEventSubscriber->updatePayPalOrder($event);
        $this->payPalOrderEventSubscriber->clearCache($event);
    }

    /**
     * Recursively compares two arrays and returns the differences.
     *
     * @param array $array1
     * @param array $array2
     * @param int $maxDepth
     * @param int $currentDepth
     *
     * @return array
     */
    private function arrayRecursiveDiff(array $array1, array $array2, $maxDepth = 5, $currentDepth = 0)
    {
        $result = [];

        if ($currentDepth >= $maxDepth) {
            return $result;
        }

        foreach ($array1 as $key => $value) {
            if (array_key_exists($key, $array2)) {
                if (is_array($value)) {
                    $recursiveDiff = $this->arrayRecursiveDiff($value, $array2[$key], $maxDepth, $currentDepth + 1);
                    if (!empty($recursiveDiff)) {
                        $result[$key] = $recursiveDiff;
                    }
                } elseif ($value !== $array2[$key]) {
                    $result[$key] = $value;
                }
            } else {
                $result[$key] = $value;
            }
        }

        return $result;
    }
}
