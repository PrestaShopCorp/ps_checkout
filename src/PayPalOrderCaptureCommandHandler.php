<?php
use PrestaShop\Module\PrestashopCheckout\Api\Payment\Order;
use PrestaShop\Module\PrestashopCheckout\Exception\PsCheckoutException;
use PrestaShop\Module\PrestashopCheckout\PayPalError;
use PrestaShop\Module\PrestashopCheckout\PayPalProcessorResponse;
use PrestaShop\Module\PrestashopCheckout\Repository\PsCheckoutCartRepository;

class PayPalOrderCaptureCommandHandler
{
    const CAPTURE_STATUS_PENDING = 'PENDING';
    const CAPTURE_STATUS_DENIED = 'DENIED';
    const CAPTURE_STATUS_VOIDED = 'VOIDED';
    const CAPTURE_STATUS_COMPLETED = 'COMPLETED';
    const CAPTURE_STATUS_DECLINED = 'DECLINED';

    /**
     * @var \PayPal\Api\Order
     */
    private $orderApi;

    /**
     * @var CacheInterface
     */
    private $paypalOrderCache;

    /**
     * @var PsCheckoutCartRepository
     */
    private $psCheckoutCartRepository;

    public function __construct(Order $orderApi, CacheInterface $paypalOrderCache, PsCheckoutCartRepository $psCheckoutCartRepository)
    {
        $this->orderApi = $orderApi;
        $this->paypalOrderCache = $paypalOrderCache;
        $this->psCheckoutCartRepository = $psCheckoutCartRepository;
    }

    /**
     * @param int $cartId
     * @param string $orderId
     * @param string $merchantId
     * @param string $intent
     *
     * @return array
     *
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     * @throws PsCheckoutException
     * @throws \PrestaShop\Module\PrestashopCheckout\Exception\PayPalException
     */
    public function handle(PayPalOrderCaptureCommand $command)
    {
        /** @var \PsCheckoutCart|false $psCheckoutCart */
        $psCheckoutCart = $this->psCheckoutCartRepository->findOneByCartId($command->getCartId());

        $fundingSource = !$psCheckoutCart ? 'paypal' : $psCheckoutCart->paypal_funding;

        if ($fundingSource === 'card') {
            $fundingSource .= $psCheckoutCart->isHostedFields ? '_hosted' : '_inline';
        }

        $response = $this->orderApi->capture(
            $command->getOrderId(),
            $command->getMerchantId(),
            $fundingSource
        );

        if (!$response['status']) {
            if (!empty($response['body']['message'])) {
                (new PayPalError($response['body']['message']))->throwException();
            }

            if (!empty($response['exceptionMessage']) && !empty($response['exceptionCode'])) {
                throw new PsCheckoutException($response['exceptionMessage'], (int) $response['exceptionCode']);
            }

            throw new PsCheckoutException(isset($response['body']['error']) ? $response['body']['error'] : 'Unknown error', PsCheckoutException::UNKNOWN);
        }

        if (!empty($response['body']['purchase_units'][0]['payments']['captures'])) {
            $transactionIdentifier = $response['body']['purchase_units'][0]['payments']['captures'][0]['id'];
            $transactionStatus = $response['body']['purchase_units'][0]['payments']['captures'][0]['status'];

            if (self::CAPTURE_STATUS_DECLINED === $transactionStatus
                && !empty($response['body']['payment_source'])
                && !empty($response['body']['payment_source'][0]['card'])
                && !empty($response['body']['purchase_units'][0]['payments']['captures'][0]['processor_response'])
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

        $this->paypalOrderCache->set($response['body']['id'], $response['body']);

        if (!$psCheckoutCart) {
            $psCheckoutCart = new \PsCheckoutCart();
            $psCheckoutCart->id_cart = $command->getCartId();
            $psCheckoutCart->paypal_intent = $command->getIntent();
            $psCheckoutCart->paypal_order = $response['body']['id'];
            $psCheckoutCart->paypal_status = $response['body']['status'];
            $this->psCheckoutCartRepository->save($psCheckoutCart);
        } else {
            $psCheckoutCart->paypal_order = $response['body']['id'];
            $psCheckoutCart->paypal_status = $response['body']['status'];
            $this->psCheckoutCartRepository->save($psCheckoutCart);
        }

        return [
            'id' => $psCheckoutCart->paypal_order,
            'status' => $psCheckoutCart->paypal_status,
            'transactionIdentifier' => !empty($transactionIdentifier) ? $transactionIdentifier : false,
            'transactionStatus' => !empty($transactionStatus) ? $transactionStatus : false,
        ];
    }
}
