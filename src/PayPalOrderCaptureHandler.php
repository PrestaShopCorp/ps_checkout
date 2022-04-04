<?php
use PrestaShop\Module\PrestashopCheckout\Api\Payment\Order;
use PrestaShop\Module\PrestashopCheckout\Exception\PsCheckoutException;
use PrestaShop\Module\PrestashopCheckout\PayPalError;
use PrestaShop\Module\PrestashopCheckout\PayPalProcessorResponse;

class PayPalOrderCaptureHandler
{
    /**
     * @var \PayPal\Api\Order
     */
    private $orderApi;

    public function __construct(Order $orderApi)
    {
        $this->orderApi = $orderApi;
    }

    public function capture($orderId, $merchantId, $fundingSource) {
        $response = $this->orderApi->capture(
            $orderId,
            $merchantId,
            $fundingSource
        );

        if (false === $response['status']) {
            if (false === empty($response['body']['message'])) {
                (new PayPalError($response['body']['message']))->throwException();
            }

            if (false === empty($response['exceptionMessage']) && false === empty($response['exceptionCode'])) {
                throw new PsCheckoutException($response['exceptionMessage'], (int) $response['exceptionCode']);
            }

            throw new PsCheckoutException(isset($response['body']['error']) ? $response['body']['error'] : 'Unknown error', PsCheckoutException::UNKNOWN);
        }

        if (false === empty($response['body']['purchase_units'][0]['payments']['captures'])) {
            $transactionIdentifier = $response['body']['purchase_units'][0]['payments']['captures'][0]['id'];
            $transactionStatus = $response['body']['purchase_units'][0]['payments']['captures'][0]['status'];

            if (self::CAPTURE_STATUS_DECLINED === $transactionStatus
                && false === empty($response['body']['payment_source'])
                && false === empty($response['body']['payment_source'][0]['card'])
                && false === empty($response['body']['purchase_units'][0]['payments']['captures'][0]['processor_response'])
            ) {
                $payPalProcessorResponse = new PayPalProcessorResponse(
                    isset($response['body']['payment_source'][0]['card']['brand']) ? $response['body']['payment_source'][0]['card']['brand'] : null,
                    isset($response['body']['payment_source'][0]['card']['type']) ? $response['body']['payment_source'][0]['card']['type'] : null,
                    isset($response['body']['purchase_units'][0]['payments']['captures'][0]['processor_response']['avs_code']) ? $response['body']['purchase_units'][0]['payments']['captures'][0]['processor_response']['avs_code'] : null,
                    isset($response['body']['purchase_units'][0]['payments']['captures'][0]['processor_response']['cvv_code']) ? $response['body']['purchase_units'][0]['payments']['captures'][0]['processor_response']['cvv_code'] : null,
                    isset($response['body']['purchase_units'][0]['payments']['captures'][0]['processor_response']['response_code']) ? $response['body']['purchase_units'][0]['payments']['captures'][0]['processor_response']['response_code'] : null
                );
                $payPalProcessorResponse->throwException();
            }
        }
    }
}
