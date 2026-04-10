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

if (!defined('_PS_VERSION_')) {
    exit;
}

use Monolog\Logger;
use PsCheckout\Core\OrderState\OrderStateException;
use PsCheckout\Core\OrderState\Service\OrderStateMapper;
use PsCheckout\Core\PayPal\Order\Action\RefundPayPalOrderAction;
use PsCheckout\Core\PayPal\Order\Cache\PayPalOrderCache;
use PsCheckout\Core\PayPal\Order\Provider\PayPalOrderProvider;
use PsCheckout\Core\PayPal\Payment\Authorization\Configuration\AuthorizationAction;
use PsCheckout\Core\PayPal\Payment\Authorization\Processor\AuthorizationActionProcessor;
use PsCheckout\Core\PayPal\Refund\Exception\Handler\RefundExceptionHandler;
use PsCheckout\Core\PayPal\Refund\ValueObject\PayPalRefund;
use PsCheckout\Core\Settings\Configuration\LoggerConfiguration;
use PsCheckout\Core\Settings\Configuration\PayPalConfiguration;
use PsCheckout\Core\Settings\Configuration\PayPalExpressCheckoutConfiguration;
use PsCheckout\Core\Settings\Configuration\PayPalPayLaterConfiguration;
use PsCheckout\Core\Webhook\Service\WebhookSecretToken;
use PsCheckout\Infrastructure\Action\SaveBatchConfigurationActionInterface;
use PsCheckout\Infrastructure\Adapter\Configuration;
use PsCheckout\Infrastructure\Bootstrap\Install\ApplePayInstaller;
use PsCheckout\Infrastructure\Bootstrap\Install\ApplePayInstallerException;
use PsCheckout\Infrastructure\Bootstrap\Install\OrderStateInstaller;
use PsCheckout\Infrastructure\Controller\AbstractAdminController;
use PsCheckout\Infrastructure\Logger\LoggerFileFinder;
use PsCheckout\Infrastructure\Logger\LoggerFileReader;
use PsCheckout\Infrastructure\Repository\FundingSourceRepository;
use PsCheckout\Infrastructure\Repository\PaymentTokenRepository;
use PsCheckout\Infrastructure\Repository\PayPalOrderRepository;
use PsCheckout\Infrastructure\Repository\PsAccountRepository;
use PsCheckout\Module\Presentation\Translator;
use PsCheckout\Presentation\Presenter\PayPalOrder\MerchantOrderViewPresenter;
use PsCheckout\Presentation\Presenter\PayPalOrder\PayPalOrderPresenter;
use Psr\Log\LoggerInterface;

class AdminAjaxPrestashopCheckoutController extends AbstractAdminController
{
    /**
     * @var Ps_Checkout
     */
    public $module;

    /**
     * @var bool
     */
    public $ajax = true;

    /**
     * @var bool
     */
    protected $json = true;

    /**
     * {@inheritDoc}
     */
    public function postProcess()
    {
        $shopIdRequested = (int) Tools::getValue('id_shop');
        $currentShopId = (int) Shop::getContextShopID();

        if ($shopIdRequested && $shopIdRequested !== $currentShopId) {
            $shopRequested = new Shop($shopIdRequested);
            if (Validate::isLoadedObject($shopRequested)) {
                Shop::setContext(Shop::CONTEXT_SHOP, $shopIdRequested);
                $this->context->shop = $shopRequested;
            }
        }

        return parent::postProcess();
    }

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        if (!isset($this->context->employee) || !$this->context->employee->isLoggedBack()) {
            // Avoid redirection to Login page because Ajax doesn't support it
            $this->initCursedPage();
        }

        parent::init();
    }

    /**
     * {@inheritdoc}
     */
    public function display()
    {
        if ($this->errors) {
            http_response_code(400);
            $this->ajaxRender(json_encode([
                'status' => false,
                'errors' => $this->errors,
            ]));
        }

        parent::display();
    }

    /**
     * {@inheritdoc}
     */
    public function initCursedPage()
    {
        http_response_code(401);

        exit;
    }

    /**
     * AJAX: Toggle payment option hosted fields availability
     */
    public function ajaxProcessTogglePaymentOptionAvailability()
    {
        $paymentOption = json_decode(Tools::getValue('paymentOption'), true);

        /** @var FundingSourceRepository $fundingSourceConfigurationRepository */
        $fundingSourceConfigurationRepository = $this->module->getService(FundingSourceRepository::class);

        $fundingSourceConfigurationRepository->upsert($paymentOption, $this->context->shop->id);

        $this->ajaxRender(json_encode(true));
    }

    /**
     * AJAX: Update payment method order
     */
    public function ajaxProcessUpdatePaymentMethodsOrder()
    {
        $paymentOptions = json_decode(Tools::getValue('paymentMethods'), true);
        /** @var FundingSourceRepository $fundingSourceConfigurationRepository */
        $fundingSourceConfigurationRepository = $this->module->getService(FundingSourceRepository::class);

        foreach ($paymentOptions as $key => $paymentOption) {
            $paymentOption['position'] = $key + 1;
            $fundingSourceConfigurationRepository->upsert($paymentOption, $this->context->shop->id);
        }

        $this->ajaxRender(json_encode(true));
    }

    /**
     * AJAX: Update payment mode (LIVE or SANDBOX)
     */
    public function ajaxProcessUpdatePaymentMode()
    {
        $paymentMode = Tools::getValue('paymentMode');

        if (!in_array($paymentMode, [PayPalConfiguration::MODE_LIVE, PayPalConfiguration::MODE_SANDBOX])) {
            throw new \UnexpectedValueException(sprintf('The value should be a Mode constant, %s value sent', $paymentMode));
        }

        $this->setConfiguration(PayPalConfiguration::PS_CHECKOUT_PAYMENT_MODE, $paymentMode);

        $this->ajaxRender(json_encode(true));
    }

    /**
     * AJAX: Change prestashop rounding settings
     *
     * PS_ROUND_TYPE need to be set to 1 (Round on each item)
     * PS_PRICE_ROUND_MODE need to be set to 2 (Round up away from zero, when it is half way there)
     */
    public function ajaxProcessEditRoundingSettings()
    {
        $this->setConfiguration(PayPalConfiguration::PS_ROUND_TYPE, PayPalConfiguration::ROUND_ON_EACH_ITEM);
        $this->setConfiguration(PayPalConfiguration::PS_PRICE_ROUND_MODE, PayPalConfiguration::ROUND_UP_AWAY_FROM_ZERO);

        $this->ajaxRender(json_encode(true));
    }

    /**
     * AJAX: Update credit card fields (Hosted fields / Smartbutton)
     */
    public function ajaxProcessUpdateCreditCardFields()
    {
        $this->setConfiguration(PayPalConfiguration::PS_CHECKOUT_CARD_PAYMENT_ENABLED, (bool) Tools::getValue('hostedFieldsEnabled'));

        $this->ajaxRender(json_encode(true));
    }

    /**
     * AJAX: Save PayPal button configuration
     */
    public function ajaxProcessSavePaypalButtonConfiguration()
    {
        $this->setConfiguration(PayPalConfiguration::PS_CHECKOUT_PAYPAL_BUTTON, Tools::getValue('configuration'));

        $this->ajaxRender(json_encode(true));
    }

    /**
     * AJAX: Get or refresh token for CDN application
     */
    public function ajaxProcessGetOrRefreshToken()
    {
        /** @var PsAccountRepository $psAccountRepository */
        $psAccountRepository = $this->module->getService(PsAccountRepository::class);

        try {
            $this->exitWithResponse([
                'httpCode' => 200,
                'status' => true,
                'token' => $psAccountRepository->getIdToken(),
                'shopId' => $psAccountRepository->getShopUuid(),
                'isAccountLinked' => $psAccountRepository->isAccountLinked(),
            ]);
        } catch (Exception $exception) {
            /** @var LoggerInterface $logger */
            $logger = $this->module->getService(LoggerInterface::class);
            $logger->error('Failed to get or refresh token: ' . $exception->getMessage(), ['exception' => $exception]);

            $this->exitWithResponse([
                'httpCode' => 500,
                'status' => false,
                'error' => sprintf(
                    '%s %d : %s',
                    get_class($exception),
                    $exception->getCode(),
                    $exception->getMessage()
                ),
            ]);
        }
    }

    public function ajaxProcessUpsertSecretToken()
    {
        /** @var WebhookSecretToken $webhookSecretTokenService */
        $webhookSecretTokenService = $this->module->getService(WebhookSecretToken::class);

        $token = (string) Tools::getValue('body');

        $response = [];

        try {
            $status = $webhookSecretTokenService->upsertToken($token);
        } catch (Exception $exception) {
            /** @var LoggerInterface $logger */
            $logger = $this->module->getService(LoggerInterface::class);
            $logger->error('Failed to upsert secret token: ' . $exception->getMessage(), ['exception' => $exception]);

            $status = false;
            $response['errors'] = $exception->getMessage();
        }

        http_response_code($status ? 204 : 500);
        $response['status'] = $status;
        $this->ajaxRender(json_encode($response));
    }

    public function ajaxProcessCheckConfiguration()
    {
        $response = [];

        $query = new DbQuery();
        $query->select('name, value, date_add, date_upd');
        $query->from('configuration');
        $query->where('name LIKE "PS_CHECKOUT_%"');

        /** @var int|null $shopId When multishop is disabled, it returns null, so we don't have to restrict results by shop */
        $shopId = Shop::getContextShopID(true);

        // When ShopId is not NULL, we have to retrieve global values with id_shop = NULL and shop values with id_shop = ShopId
        if ($shopId) {
            $query->where('id_shop IS NULL OR id_shop = ' . (int) $shopId);
        }

        $configurations = Db::getInstance()->executeS($query);

        $response['status'] = !empty($configurations);

        foreach ($configurations as $configuration) {
            $response['configuration'][] = [
                'name' => $configuration['name'],
                'value' => !empty($configuration['value']),
            ];
        }

        $this->exitWithResponse($response);
    }

    public function ajaxProcessFetchConfiguration()
    {
        $query = new DbQuery();
        $query->select('name, value, date_add, date_upd');
        $query->from('configuration');
        $query->where('name LIKE "PS_CHECKOUT_%"');

        /** @var int|null $shopId When multishop is disabled, it returns null, so we don't have to restrict results by shop */
        $shopId = Shop::getContextShopID(true);

        // When ShopId is not NULL, we have to retrieve global values with id_shop = NULL and shop values with id_shop = ShopId
        if ($shopId) {
            $query->where('id_shop IS NULL OR id_shop = ' . (int) $shopId);
        }

        $configurations = Db::getInstance()->executeS($query);

        $response = [
            'httpCode' => 200,
            'status' => !empty($configurations),
            'configuration' => array_map(function ($configuration) {
                return [
                    'name' => $configuration['name'],
                    'value' => $configuration['value'],
                ];
            }, $configurations),
        ];

        $this->exitWithResponse($response);
    }

    public function ajaxProcessGetMappedOrderStates()
    {
        /** @var OrderStateMapper $orderStateMapper */
        $orderStateMapper = $this->module->getService(OrderStateMapper::class);
        $mappedOrderStates = [];

        try {
            $mappedOrderStates = $orderStateMapper->getMappedOrderStates();
        } catch (OrderStateException $exception) {
            /** @var LoggerInterface $logger */
            $logger = $this->module->getService(LoggerInterface::class);
            $logger->error('Failed to get mapped order states: ' . $exception->getMessage(), ['exception' => $exception]);

            if ($exception->getCode() === OrderStateException::INVALID_MAPPING) {
                /** @var OrderStateInstaller $orderStateInstaller */
                $orderStateInstaller = $this->module->getService(OrderStateInstaller::class);
                $orderStateInstaller->init();
            }

            $this->exitWithResponse([
                'httpCode' => 500,
                'status' => false,
                'error' => $exception->getMessage(),
            ]);
        }

        $this->exitWithResponse([
            'status' => true,
            'mappedOrderStates' => $mappedOrderStates,
        ]);
    }

    public function ajaxProcessBatchSaveConfiguration()
    {
        /** @var SaveBatchConfigurationActionInterface $saveBatchConfigurationAction */
        $saveBatchConfigurationAction = $this->module->getService(SaveBatchConfigurationActionInterface::class);

        $configuration = json_decode(Tools::getValue('configuration'), true);

        try {
            $saveBatchConfigurationAction->execute($configuration);

            $this->exitWithResponse([
                'status' => true,
            ]);
        } catch (Exception $exception) {
            /** @var LoggerInterface $logger */
            $logger = $this->module->getService(LoggerInterface::class);
            $logger->error('Failed to save batch configuration: ' . $exception->getMessage(), ['exception' => $exception]);

            $this->exitWithResponse([
                'httpCode' => 500,
                'status' => false,
                'error' => $exception->getMessage(),
            ]);
        }
    }

    public function ajaxProcessGetOrderStates()
    {
        $orderStates = OrderState::getOrderStates($this->context->language->id);

        $this->exitWithResponse([
            'status' => true,
            'orderStates' => $orderStates,
        ]);
    }

    public function ajaxProcessGetPaymentTokenCount()
    {
        /** @var Configuration $configuration */
        $configuration = $this->module->getService(Configuration::class);

        /** @var PaymentTokenRepository $paymentTokenRepository */
        $paymentTokenRepository = $this->module->getService(PaymentTokenRepository::class);

        $this->exitWithResponse([
            'status' => true,
            'count' => $paymentTokenRepository->getCount(
                null,
                $configuration->get(PayPalConfiguration::PS_CHECKOUT_PAYPAL_ID_MERCHANT)
            ),
        ]);
    }

    /**
     * AJAX: Update logger level
     */
    public function ajaxProcessUpdateLoggerLevel()
    {
        $levels = [
            Logger::DEBUG,
            Logger::INFO,
            Logger::NOTICE,
            Logger::WARNING,
            Logger::ERROR,
            Logger::CRITICAL,
            Logger::ALERT,
            Logger::EMERGENCY,
        ];

        $level = (int) Tools::getValue('level');

        if (false === in_array($level, $levels, true)) {
            http_response_code(400);
            $this->ajaxRender(json_encode([
                'status' => false,
                'errors' => [
                    'Logger level is invalid',
                ],
            ]));
        }

        $this->setConfiguration(LoggerConfiguration::PS_CHECKOUT_LOGGER_LEVEL, $level);

        $this->ajaxRender(json_encode([
            'status' => true,
            'content' => [
                'level' => $level,
            ],
        ]));
    }

    /**
     * AJAX: Update logger max files
     */
    public function ajaxProcessUpdateLoggerMaxFiles()
    {
        $maxFiles = (int) Tools::getValue('maxFiles');

        if ($maxFiles < 0 || $maxFiles > 30) {
            http_response_code(400);
            $this->ajaxRender(json_encode([
                'status' => false,
                'errors' => [
                    'Logger max files is invalid',
                ],
            ]));
        }

        $this->setConfiguration(LoggerConfiguration::PS_CHECKOUT_LOGGER_MAX_FILES, $maxFiles);

        $this->ajaxRender(json_encode([
            'status' => true,
            'content' => [
                'maxFiles' => $maxFiles,
            ],
        ]));
    }

    /**
     * AJAX: Update logger http
     */
    public function ajaxProcessUpdateLoggerHttp()
    {
        $isEnabled = (int) Tools::getValue('isEnabled');

        $this->setConfiguration(LoggerConfiguration::PS_CHECKOUT_LOGGER_HTTP, $isEnabled);

        $this->ajaxRender(json_encode([
            'status' => true,
            'content' => [
                'isEnabled' => $isEnabled,
            ],
        ]));
    }

    /**
     * AJAX: Update logger http format
     */
    public function ajaxProcessUpdateLoggerHttpFormat()
    {
        $formats = [
            'CLF',
            'DEBUG',
            'SHORT',
        ];

        $format = Tools::getValue('httpFormat');

        if (false === in_array($format, $formats, true)) {
            $this->ajaxRender(json_encode([
                'status' => false,
                'errors' => [
                    'Logger http format is invalid',
                ],
            ]));
        }

        $this->setConfiguration(LoggerConfiguration::PS_CHECKOUT_LOGGER_HTTP_FORMAT, $format);

        $this->ajaxRender(json_encode([
            'status' => true,
            'content' => [
                'httpFormat' => $format,
            ],
        ]));
    }

    /**
     * AJAX: Get logs files
     */
    public function ajaxProcessGetLogFiles()
    {
        /** @var LoggerFileFinder $loggerFileFinder */
        $loggerFileFinder = $this->module->getService(LoggerFileFinder::class);

        header('Content-type: application/json');
        $this->ajaxRender(json_encode($loggerFileFinder->getFiles()));
    }

    /**
     * AJAX: Read a log file
     */
    public function ajaxProcessGetLogs()
    {
        $filename = Tools::getValue('file');
        $offset = (int) Tools::getValue('offset');
        $limit = (int) Tools::getValue('limit');

        /** @var LoggerFileReader $loggerFileReader */
        $loggerFileReader = $this->module->getService(LoggerFileReader::class);
        $fileData = [];

        try {
            $fileData = $loggerFileReader->read(
                $filename,
                $offset,
                $limit
            );
        } catch (InvalidArgumentException $exception) {
            $this->exitWithResponse([
                'status' => false,
                'httpCode' => 400,
                'errors' => [
                    $exception->getMessage(),
                ],
            ]);
        } catch (Exception $exception) {
            /** @var LoggerInterface $logger */
            $logger = $this->module->getService(LoggerInterface::class);
            $logger->error('Failed to read log file: ' . $exception->getMessage(), ['exception' => $exception]);

            $this->exitWithResponse([
                'status' => false,
                'httpCode' => 500,
                'errors' => [
                    $exception->getMessage(),
                ],
            ]);
        }

        $this->exitWithResponse([
            'status' => true,
            'file' => $fileData['filename'],
            'offset' => $fileData['offset'],
            'limit' => $fileData['limit'],
            'currentOffset' => $fileData['currentOffset'],
            'eof' => (int) $fileData['eof'],
            'lines' => $fileData['lines'],
        ]);
    }

    /**
     * AJAX: Toggle express checkout on order page
     */
    public function ajaxProcessToggleECOrderPage()
    {
        $this->setConfiguration(PayPalExpressCheckoutConfiguration::PS_CHECKOUT_EC_ORDER_PAGE, (bool) Tools::getValue('status'));

        $this->ajaxRender(json_encode(true));
    }

    /**
     * AJAX: Toggle express checkout on checkout page
     */
    public function ajaxProcessToggleECCheckoutPage()
    {
        $this->setConfiguration(PayPalExpressCheckoutConfiguration::PS_CHECKOUT_EC_CHECKOUT_PAGE, (bool) Tools::getValue('status'));

        $this->ajaxRender(json_encode(true));
    }

    /**
     * AJAX: Toggle express checkout on product page
     */
    public function ajaxProcessToggleECProductPage()
    {
        $this->setConfiguration(PayPalExpressCheckoutConfiguration::PS_CHECKOUT_EC_PRODUCT_PAGE, (bool) Tools::getValue('status'));

        $this->ajaxRender(json_encode(true));
    }

    /**
     * AJAX: Toggle pay later button on cart page
     */
    public function ajaxProcessTogglePayLaterCartPageButton()
    {
        $this->setConfiguration(PayPalPayLaterConfiguration::PS_CHECKOUT_PAY_LATER_CART_PAGE_BUTTON, (bool) Tools::getValue('status'));

        $this->ajaxRender(json_encode(true));
    }

    /**
     * AJAX: Toggle pay later button on order page
     */
    public function ajaxProcessTogglePayLaterOrderPageButton()
    {
        $this->setConfiguration(PayPalPayLaterConfiguration::PS_CHECKOUT_PAY_LATER_ORDER_PAGE_BUTTON, (bool) Tools::getValue('status'));

        $this->ajaxRender(json_encode(true));
    }

    /**
     * AJAX: Toggle pay later button on product page
     */
    public function ajaxProcessTogglePayLaterProductPageButton()
    {
        $this->setConfiguration(PayPalPayLaterConfiguration::PS_CHECKOUT_PAY_LATER_PRODUCT_PAGE_BUTTON, (bool) Tools::getValue('status'));

        $this->ajaxRender(json_encode(true));
    }

    public function ajaxProcessFetchOrder()
    {
        /** @var Translator $translator **/
        $translator = $this->module->getService(Translator::class);
        $id_order = (int) Tools::getValue('id_order');

        if (empty($id_order)) {
            http_response_code(400);
            $this->ajaxRender(json_encode([
                'status' => false,
                'errors' => [
                    $translator->trans('No PrestaShop Order identifier received'),
                ],
            ]));
        }

        $order = new Order($id_order);

        /** @var PayPalOrderRepository $payPalOrderRepository */
        $payPalOrderRepository = $this->module->getService(PayPalOrderRepository::class);
        $payPalOrder = $payPalOrderRepository->getOneByCartId($order->id_cart);

        if (!$payPalOrder) {
            try {
                $migrationSuccessful = $payPalOrderRepository->attemptToMigratePsCheckoutCart($order->id_cart);
                if ($migrationSuccessful) {
                    $payPalOrder = $payPalOrderRepository->getOneByCartId($order->id_cart);
                }
            } catch (\Exception $e) {
                $logger = $this->module->getService(LoggerInterface::class);
                $logger->error(
                    'Attempted to migrate order to V5 database structure. Encountered error: ' . $e->getMessage(),
                    [
                        'exception' => $e,
                        'cart_id' => $order->id_cart,
                    ]
                );
            }
        }

        if (!$payPalOrder) {
            http_response_code(500);
            $this->ajaxRender(json_encode([
                'status' => false,
                'errors' => [
                    $translator->trans('Unable to find PayPal Order associated to this PrestaShop Order %s', [$order->id]),
                ],
            ]));
        }

        /** @var Configuration $configuration */
        $configuration = $this->module->getService(Configuration::class);

        if ($configuration->get(PayPalConfiguration::PS_CHECKOUT_PAYMENT_MODE) !== $payPalOrder->getEnvironment()) {
            http_response_code(422);
            $this->ajaxRender(json_encode([
                'status' => false,
                'errors' => [
                    $translator->trans('PayPal Order %s is not in the same environment as PrestaShop Checkout', [$payPalOrder->getId()]),
                ],
            ]));
        }

        /** @var PayPalOrderProvider $paypalOrderProvider */
        $paypalOrderProvider = $this->module->getService(PayPalOrderProvider::class);

        try {
            $paypalOrderResponse = $paypalOrderProvider->getById($payPalOrder->getId());
        } catch (Exception $exception) {
            /** @var LoggerInterface $logger */
            $logger = $this->module->getService(LoggerInterface::class);
            $logger->error('Failed to fetch PayPal order: ' . $exception->getMessage(), ['exception' => $exception]);

            http_response_code(500);
            $this->ajaxRender(json_encode([
                'status' => false,
                'errors' => [$exception->getMessage()],
            ]));
        }

        /** @var PayPalOrderPresenter $payPalOrderPresenter */
        $payPalOrderPresenter = $this->module->getService(PayPalOrderPresenter::class);

        $this->context->smarty->assign([
            'orderPayPal' => $payPalOrderPresenter->present($paypalOrderResponse),
            'psPayPalOrder' => $payPalOrder,
            'isProductionEnv' => $payPalOrder->getEnvironment() === PayPalConfiguration::MODE_LIVE,
            'moduleLogoUri' => $this->module->getPathUri() . 'logo.png',
            'moduleUrl' => $this->context->link->getAdminLink('AdminModules', true, [], ['configure' => $this->module->name]),
            'orderPayPalBaseUrl' => $this->context->link->getAdminLink('AdminAjaxPrestashopCheckout'),
        ]);

        $this->ajaxRender(json_encode([
            'status' => true,
            'content' => $this->context->smarty->fetch($this->module->getLocalPath() . 'views/templates/admin/ajaxPayPalOrder.tpl'),
        ]));
    }

    public function ajaxProcessGetOrderViewData()
    {
        $id_order = (int) Tools::getValue('id_order');

        list($payPalOrder, $paypalOrderResponse, $isProductionEnv) = $this->resolvePayPalOrder($id_order);

        /** @var MerchantOrderViewPresenter $presenter */
        $presenter = $this->module->getService(MerchantOrderViewPresenter::class);
        $data = $presenter->present($paypalOrderResponse, $isProductionEnv);

        $this->exitWithResponse([
            'httpCode' => 200,
            'status' => true,
            'order' => $data['order'],
            'isTestMode' => $data['isTestMode'],
        ]);
    }

    public function ajaxProcessRefundOrder()
    {
        /** @var Translator $translator **/
        $translator = $this->module->getService(Translator::class);

        $id_order = (int) Tools::getValue('id_order');
        $captureId = Tools::getValue('id_transaction') ?: Tools::getValue('orderPayPalRefundTransaction');
        $amount = Tools::getValue('amount') ?: Tools::getValue('orderPayPalRefundAmount');

        list($payPalOrder, $paypalOrderResponse, $isProductionEnv) = $this->resolvePayPalOrder($id_order);

        $payPalOrderId = $payPalOrder->getId();
        $currency = Tools::getValue('currency') ?: Tools::getValue('orderPayPalRefundCurrency');

        if (!$currency) {
            $orderAmount = $paypalOrderResponse->getOrderAmount();
            $currency = $orderAmount['currency_code'] ?? '';
        }

        /** @var RefundPayPalOrderAction $refundPayPalOrderAction */
        $refundPayPalOrderAction = $this->module->getService(RefundPayPalOrderAction::class);

        try {
            $payPalRefund = new PayPalRefund($payPalOrderId, $captureId, $currency, $amount);

            $refundPayPalOrderAction->execute($payPalRefund);
        } catch (\Exception $exception) {
            /** @var LoggerInterface $logger */
            $logger = $this->module->getService(LoggerInterface::class);
            $logger->error('Failed to refund PayPal order: ' . $exception->getMessage(), ['exception' => $exception]);

            /** @var RefundExceptionHandler $refundExceptionHandler */
            $refundExceptionHandler = $this->module->getService(RefundExceptionHandler::class);

            $this->exitWithResponse($refundExceptionHandler->handle($exception));
        }

        $this->exitWithRefreshedOrderData($payPalOrderId, $isProductionEnv, $translator->trans('Refund has been processed by PayPal.'));
    }

    /**
     * {@inheritdoc}
     */
    public function isAnonymousAllowed(): bool
    {
        return false;
    }

    /**
     * @param string|null $value
     * @param string|null $controller
     * @param string|null $method
     *
     * @throws PrestaShopException
     */
    protected function ajaxRender($value = null, $controller = null, $method = null)
    {
        parent::ajaxRender($value, $controller, $method);

        exit;
    }

    /**
     * @param int $idOrder
     *
     * @return array{0: \PsCheckout\Infrastructure\Repository\PayPalOrder, 1: \PsCheckout\Api\ValueObject\PayPalOrderResponse, 2: bool}
     */
    private function resolvePayPalOrder(int $idOrder): array
    {
        /** @var Translator $translator */
        $translator = $this->module->getService(Translator::class);

        if (empty($idOrder)) {
            $this->exitWithResponse([
                'httpCode' => 400,
                'status' => false,
                'errors' => [
                    $translator->trans('No PrestaShop Order identifier received'),
                ],
            ]);
        }

        $order = new Order($idOrder);

        /** @var PayPalOrderRepository $payPalOrderRepository */
        $payPalOrderRepository = $this->module->getService(PayPalOrderRepository::class);
        $payPalOrder = $payPalOrderRepository->getOneByCartId($order->id_cart);

        if (!$payPalOrder) {
            try {
                $migrationSuccessful = $payPalOrderRepository->attemptToMigratePsCheckoutCart($order->id_cart);
                if ($migrationSuccessful) {
                    $payPalOrder = $payPalOrderRepository->getOneByCartId($order->id_cart);
                }
            } catch (\Exception $e) {
                $logger = $this->module->getService(LoggerInterface::class);
                $logger->error(
                    'Attempted to migrate order to V5 database structure. Encountered error: ' . $e->getMessage(),
                    [
                        'exception' => $e,
                        'cart_id' => $order->id_cart,
                    ]
                );
            }
        }

        if (!$payPalOrder) {
            $this->exitWithResponse([
                'httpCode' => 500,
                'status' => false,
                'errors' => [
                    $translator->trans('Unable to find PayPal Order associated to this PrestaShop Order %s', [$order->id]),
                ],
            ]);
        }

        /** @var Configuration $configuration */
        $configuration = $this->module->getService(Configuration::class);

        if ($configuration->get(PayPalConfiguration::PS_CHECKOUT_PAYMENT_MODE) !== $payPalOrder->getEnvironment()) {
            $this->exitWithResponse([
                'httpCode' => 422,
                'status' => false,
                'errors' => [
                    $translator->trans('PayPal Order %s is not in the same environment as PrestaShop Checkout', [$payPalOrder->getId()]),
                ],
            ]);
        }

        /** @var PayPalOrderProvider $paypalOrderProvider */
        $paypalOrderProvider = $this->module->getService(PayPalOrderProvider::class);

        try {
            $paypalOrderResponse = $paypalOrderProvider->getById($payPalOrder->getId());
        } catch (\Exception $exception) {
            /** @var LoggerInterface $logger */
            $logger = $this->module->getService(LoggerInterface::class);
            $logger->error('Failed to fetch PayPal order: ' . $exception->getMessage(), ['exception' => $exception]);

            $this->exitWithResponse([
                'httpCode' => 500,
                'status' => false,
                'errors' => [$exception->getMessage()],
            ]);
        }

        $isProductionEnv = $payPalOrder->getEnvironment() === PayPalConfiguration::MODE_LIVE;

        return [$payPalOrder, $paypalOrderResponse, $isProductionEnv];
    }

    /**
     * @param string $key
     * @param mixed $value
     * @return void
     */
    private function setConfiguration(string $key, $value)
    {
        /** @var Configuration $configuration */
        $configuration = $this->module->getService(Configuration::class);
        $configuration->set($key, $value);
    }

    /**
     * @param array $response
     *
     * @return void
     */
    private function exitWithResponse(array $response)
    {
        header('Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
        header('Content-Type: application/json;charset=utf-8');
        header('X-Robots-Tag: noindex, nofollow');

        if (isset($response['httpCode'])) {
            http_response_code($response['httpCode']);
            unset($response['httpCode']);
        }

        if (!empty($response)) {
            echo json_encode($response);
        }

        exit;
    }

    /**
     * @return void
     */
    public function ajaxProcessDownloadLogs()
    {
        $filename = Tools::getValue('file');

        try {
            /** @var LoggerFileReader $loggerFileReader */
            $loggerFileReader = $this->module->getService(LoggerFileReader::class);
            $loggerFileReader->validateFilename($filename);
        } catch (InvalidArgumentException $exception) {
            $this->exitWithResponse([
                'status' => false,
                'httpCode' => 400,
                'errors' => [
                    $exception->getMessage(),
                ],
            ]);
        }

        $file = new SplFileObject(LoggerFileFinder::LOGGER_DIRECTORY_PATH . $filename);

        if (false === $file->isReadable()) {
            $this->exitWithResponse([
                'status' => false,
                'httpCode' => 500,
                'errors' => [
                    'File is not readable.',
                ],
            ]);
        }

        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $filename . '.log"');
        header('Content-Length: ' . $file->getSize());
        readfile($file->getRealPath());

        exit;
    }

    /**
     * @return void
     */
    public function ajaxProcessSetupApplePay()
    {
        /** @var ApplePayInstaller $applePayInstaller */
        $applePayInstaller = $this->module->getService(ApplePayInstaller::class);

        try {
            $applePayInstaller->setup();
        } catch (ApplePayInstallerException $e) {
            /** @var LoggerInterface $logger */
            $logger = $this->module->getService(LoggerInterface::class);
            $logger->error('Failed to setup Apple Pay: ' . $e->getMessage(), ['exception' => $e]);

            $this->exitWithResponse([
                'httpCode' => 500,
                'status' => false,
                'error' => [
                    'message' => $e->getMessage(),
                    'code' => $e->getCode(),
                ],
            ]);
        }

        $this->exitWithResponse([
            'status' => true,
        ]);
    }

    public function ajaxProcessCaptureAuthorization()
    {
        $id_order = (int) Tools::getValue('id_order');

        list($payPalOrder, $paypalOrderResponse, $isProductionEnv) = $this->resolvePayPalOrder($id_order);

        $payload = [];
        $amount = Tools::getValue('amount');
        $currency = Tools::getValue('currency');

        if ($amount) {
            $payload['amount'] = [
                'value' => $amount,
                'currency_code' => $currency ?: $paypalOrderResponse->getOrderAmount()['currency_code'],
            ];
        }

        /** @var AuthorizationActionProcessor $processor */
        $processor = $this->module->getService(AuthorizationActionProcessor::class);
        $result = $processor->process(AuthorizationAction::CAPTURE, $payPalOrder->getId(), $payload);

        if (!$result['status']) {
            $this->exitWithResponse($result);
        }

        /** @var Translator $translator */
        $translator = $this->module->getService(Translator::class);

        $this->exitWithRefreshedOrderData($payPalOrder->getId(), $isProductionEnv, $translator->trans('Authorization captured successfully.'));
    }

    public function ajaxProcessVoidAuthorization()
    {
        $id_order = (int) Tools::getValue('id_order');

        list($payPalOrder, , $isProductionEnv) = $this->resolvePayPalOrder($id_order);

        /** @var AuthorizationActionProcessor $processor */
        $processor = $this->module->getService(AuthorizationActionProcessor::class);
        $result = $processor->process(AuthorizationAction::VOID, $payPalOrder->getId());

        if (!$result['status']) {
            $this->exitWithResponse($result);
        }

        /** @var Translator $translator */
        $translator = $this->module->getService(Translator::class);

        $this->exitWithRefreshedOrderData($payPalOrder->getId(), $isProductionEnv, $translator->trans('Authorization voided successfully.'));
    }

    public function ajaxProcessReauthorizeAuthorization()
    {
        $id_order = (int) Tools::getValue('id_order');

        list($payPalOrder, , $isProductionEnv) = $this->resolvePayPalOrder($id_order);

        /** @var AuthorizationActionProcessor $processor */
        $processor = $this->module->getService(AuthorizationActionProcessor::class);
        $result = $processor->process(AuthorizationAction::REAUTHORIZE, $payPalOrder->getId());

        if (!$result['status']) {
            $this->exitWithResponse($result);
        }

        /** @var Translator $translator */
        $translator = $this->module->getService(Translator::class);

        $this->exitWithRefreshedOrderData($payPalOrder->getId(), $isProductionEnv, $translator->trans('Authorization reauthorized successfully.'));
    }

    /**
     * @param string $payPalOrderId
     * @param bool $isProductionEnv
     * @param string $message
     *
     * @return void
     */
    private function exitWithRefreshedOrderData(string $payPalOrderId, bool $isProductionEnv, string $message)
    {
        /** @var PayPalOrderCache $cache */
        $cache = $this->module->getService(PayPalOrderCache::class);
        $cache->delete($payPalOrderId);

        /** @var PayPalOrderProvider $paypalOrderProvider */
        $paypalOrderProvider = $this->module->getService(PayPalOrderProvider::class);

        try {
            $paypalOrderResponse = $paypalOrderProvider->getById($payPalOrderId);
        } catch (\Exception $exception) {
            /** @var LoggerInterface $logger */
            $logger = $this->module->getService(LoggerInterface::class);
            $logger->error('Failed to refresh PayPal order data: ' . $exception->getMessage(), ['exception' => $exception]);

            $this->exitWithResponse([
                'httpCode' => 200,
                'status' => true,
                'message' => $message,
            ]);
        }

        /** @var MerchantOrderViewPresenter $presenter */
        $presenter = $this->module->getService(MerchantOrderViewPresenter::class);
        $data = $presenter->present($paypalOrderResponse, $isProductionEnv);

        $this->exitWithResponse([
            'httpCode' => 200,
            'status' => true,
            'message' => $message,
            'order' => $data['order'],
            'isTestMode' => $data['isTestMode'],
        ]);
    }
}
