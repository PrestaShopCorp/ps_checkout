<?php

use PrestaShop\Module\PrestashopCheckout\Exception\PsCheckoutException;

class ChangeOrderStateHandler
{
    const CAPTURE_STATUS_PENDING = 'PENDING';
    const CAPTURE_STATUS_DENIED = 'DENIED';
    const CAPTURE_STATUS_VOIDED = 'VOIDED';
    const CAPTURE_STATUS_COMPLETED = 'COMPLETED';
    const CAPTURE_STATUS_DECLINED = 'DECLINED';

    /**
     * @param int $orderId
     * @param string $transactionStatus
     * @return void
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public function changeOrderState($orderId, $transactionStatus) {
        if (in_array($transactionStatus, [self::CAPTURE_STATUS_COMPLETED, self::CAPTURE_STATUS_DECLINED])) {
            $newOrderState = $this->getNewOrderState($orderId, $transactionStatus);

            $order = new \Order($orderId);
            $currentOrderStateId = (int) $order->getCurrentState();

            // If have to change current OrderState from Waiting to Paid or Canceled
            if ($currentOrderStateId !== $newOrderState) {
                $orderHistory = new \OrderHistory();
                $orderHistory->id_order = $orderId;
                try {
                    $orderHistory->changeIdOrderState($newOrderState, $orderId);
                    $orderHistory->addWithemail();
                } catch (\ErrorException $exception) {
                    // Notice or warning from PHP
                    // For example : https://github.com/PrestaShop/PrestaShop/issues/18837
                    $exceptionHandler->handle($exception, false);
                } catch (\Exception $exception) {
                    $exceptionHandler->handle(new PsCheckoutException('Unable to change PrestaShop OrderState', PsCheckoutException::PRESTASHOP_ORDER_STATE_ERROR, $exception));
                }
            }
        }
    }

    /**
     * @param int $orderId Order identifier
     *
     * @return int OrderState identifier
     *
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    private function getPaidStatusId($orderId)
    {
        $order = new \Order($orderId);

        if (\Validate::isLoadedObject($order) && $order->getCurrentState() == \Configuration::getGlobalValue('PS_OS_OUTOFSTOCK_UNPAID')) {
            return (int) \Configuration::getGlobalValue('PS_OS_OUTOFSTOCK_PAID');
        }

        return (int) \Configuration::getGlobalValue('PS_OS_PAYMENT');
    }

    /**
     * @param int $orderId
     * @param string $transactionStatus
     * @return int
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    private function getNewOrderState($orderId, $transactionStatus)
    {
        return self::CAPTURE_STATUS_COMPLETED === $transactionStatus ?
            $this->getPaidStatusId($orderId)
            : (int) \Configuration::getGlobalValue('PS_OS_ERROR');
    }
}
