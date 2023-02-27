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
use Monolog\Logger;
use PrestaShop\Module\PrestashopCheckout\Logger\LoggerDirectory;
use PrestaShop\Module\PrestashopCheckout\Logger\LoggerFactory;
use PrestaShop\Module\PrestashopCheckout\Logger\LoggerFileFinder;
use PrestaShop\Module\PrestashopCheckout\Logger\LoggerFileReader;
use PrestaShop\Module\PrestashopCheckout\Presenter\Order\OrderPresenter;
use PrestaShop\Module\PrestashopCheckout\Settings\RoundingSettings;
use Psr\SimpleCache\CacheInterface;

class AdminAjaxPrestashopCheckoutController extends ModuleAdminController
{
    /**
     * @var Ps_checkout
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
     * AJAX: Update payment method order
     */
    public function ajaxProcessUpdatePaymentMethodsOrder()
    {
        $paymentOptions = json_decode(Tools::getValue('paymentMethods'), true);
        /** @var PrestaShop\Module\PrestashopCheckout\FundingSource\FundingSourceConfigurationRepository $fundingSourceConfigurationRepository */
        $fundingSourceConfigurationRepository = $this->module->getService('ps_checkout.funding_source.configuration.repository');

        foreach ($paymentOptions as $key => $paymentOption) {
            $paymentOption['position'] = $key + 1;
            $fundingSourceConfigurationRepository->save($paymentOption);
        }

        $this->ajaxDie(json_encode(true));
    }

    /**
     * AJAX: Update payment mode (LIVE or SANDBOX)
     */
    public function ajaxProcessUpdatePaymentMode()
    {
        /** @var PrestaShop\Module\PrestashopCheckout\PayPal\PayPalConfiguration $paypalConfiguration */
        $paypalConfiguration = $this->module->getService('ps_checkout.paypal.configuration');
        $paypalConfiguration->setPaymentMode(Tools::getValue('paymentMode'));

        $this->ajaxDie(json_encode(true));
    }

    /**
     * AJAX: Confirm PS Live Step Banner closed
     */
    public function ajaxProcessLiveStepConfirmed()
    {
        /** @var \PrestaShop\Module\PrestashopCheckout\OnBoarding\Step\LiveStep $stepLive */
        $stepLive = $this->module->getService('ps_checkout.step.live');
        $stepLive->confirmed(true);

        $this->ajaxDie(json_encode(true));
    }

    /**
     * AJAX: Confirm PS Live Step fist time
     */
    public function ajaxProcessLiveStepViewed()
    {
        /** @var \PrestaShop\Module\PrestashopCheckout\OnBoarding\Step\LiveStep $stepLive */
        $stepLive = $this->module->getService('ps_checkout.step.live');
        $stepLive->viewed(true);

        $this->ajaxDie(json_encode(true));
    }

    /**
     * AJAX: Confirm PS Value Banner closed
     */
    public function ajaxProcessValueBannerClosed()
    {
        /** @var \PrestaShop\Module\PrestashopCheckout\OnBoarding\Step\ValueBanner $valueBanner */
        $valueBanner = $this->module->getService('ps_checkout.step.value');
        $valueBanner->closed(true);

        $this->ajaxDie(json_encode(true));
    }

    /**
     * AJAX: Change prestashop rounding settings
     *
     * PS_ROUND_TYPE need to be set to 1 (Round on each item)
     * PS_PRICE_ROUND_MODE need to be set to 2 (Round up away from zero, wh
     */
    public function ajaxProcessEditRoundingSettings()
    {
        /** @var PrestaShop\Module\PrestashopCheckout\PayPal\PayPalConfiguration $paypalConfiguration */
        $paypalConfiguration = $this->module->getService('ps_checkout.paypal.configuration');
        $paypalConfiguration->setRoundType(RoundingSettings::ROUND_ON_EACH_ITEM);
        $paypalConfiguration->setPriceRoundMode(RoundingSettings::ROUND_UP_AWAY_FROM_ZERO);

        $this->ajaxDie(json_encode(true));
    }

    /**
     * AJAX: Retrieve Reporting informations
     */
    public function ajaxProcessGetReportingDatas()
    {
        try {
            /** @var PrestaShop\Module\PrestashopCheckout\Presenter\Order\OrderPendingPresenter $pendingOrder */
            $pendingOrder = $this->module->getService('ps_checkout.presenter.order.pending');
            /** @var PrestaShop\Module\PrestashopCheckout\Presenter\Transaction\TransactionPresenter $transactionOrder */
            $transactionOrder = $this->module->getService('ps_checkout.presenter.transaction');
            $this->ajaxDie(
                json_encode([
                    'orders' => $pendingOrder->present(),
                    'transactions' => $transactionOrder->present(),
                ])
            );
        } catch (Exception $exception) {
            http_response_code(500);
            $this->ajaxDie(json_encode(strip_tags($exception->getMessage())));
        }
    }

    /**
     * AJAX: Toggle payment option hosted fields availability
     */
    public function ajaxProcessTogglePaymentOptionAvailability()
    {
        $paymentOption = json_decode(Tools::getValue('paymentOption'), true);

        /** @var PrestaShop\Module\PrestashopCheckout\FundingSource\FundingSourceConfigurationRepository $fundingSourceConfigurationRepository */
        $fundingSourceConfigurationRepository = $this->module->getService('ps_checkout.funding_source.configuration.repository');

        $fundingSourceConfigurationRepository->save($paymentOption);

        $this->ajaxDie(json_encode(true));
    }

    /**
     * AJAX: Update credit card fields (Hosted fields / Smartbutton)
     */
    public function ajaxProcessUpdateCreditCardFields()
    {
        /** @var PrestaShop\Module\PrestashopCheckout\PayPal\PayPalConfiguration $paypalConfiguration */
        $paypalConfiguration = $this->module->getService('ps_checkout.paypal.configuration');

        $paypalConfiguration->setCardPaymentEnabled((bool) Tools::getValue('hostedFieldsEnabled'));

        $this->ajaxDie(json_encode(true));
    }

    /**
     * AJAX: Toggle express checkout on order page
     */
    public function ajaxProcessToggleECOrderPage()
    {
        /** @var \PrestaShop\Module\PrestashopCheckout\ExpressCheckout\ExpressCheckoutConfiguration $ecConfiguration */
        $ecConfiguration = $this->module->getService('ps_checkout.express_checkout.configuration');
        $ecConfiguration->setOrderPage((bool) Tools::getValue('status'));

        (new PrestaShop\Module\PrestashopCheckout\Api\Payment\Shop(Context::getContext()->link))->updateSettings();

        $this->ajaxDie(json_encode(true));
    }

    /**
     * AJAX: Toggle express checkout on checkout page
     */
    public function ajaxProcessToggleECCheckoutPage()
    {
        /** @var \PrestaShop\Module\PrestashopCheckout\ExpressCheckout\ExpressCheckoutConfiguration $ecConfiguration */
        $ecConfiguration = $this->module->getService('ps_checkout.express_checkout.configuration');
        $ecConfiguration->setCheckoutPage(Tools::getValue('status') ? true : false);

        (new PrestaShop\Module\PrestashopCheckout\Api\Payment\Shop(Context::getContext()->link))->updateSettings();

        $this->ajaxDie(json_encode(true));
    }

    /**
     * AJAX: Toggle express checkout on product page
     */
    public function ajaxProcessToggleECProductPage()
    {
        /** @var \PrestaShop\Module\PrestashopCheckout\ExpressCheckout\ExpressCheckoutConfiguration $ecConfiguration */
        $ecConfiguration = $this->module->getService('ps_checkout.express_checkout.configuration');
        $ecConfiguration->setProductPage(Tools::getValue('status') ? true : false);

        (new PrestaShop\Module\PrestashopCheckout\Api\Payment\Shop(Context::getContext()->link))->updateSettings();

        $this->ajaxDie(json_encode(true));
    }

    /**
     * AJAX: Toggle pay later message on order page
     */
    public function ajaxProcessTogglePayLaterOrderPageMessage()
    {
        $this->togglePayLaterConfiguration('setOrderPageMessage');
    }

    /**
     * AJAX: Toggle pay later message on product page
     */
    public function ajaxProcessTogglePayLaterProductPageMessage()
    {
        $this->togglePayLaterConfiguration('setProductPageMessage');
    }

    /**
     * AJAX: Toggle pay later banner on cart page
     */
    public function ajaxProcessTogglePayLaterOrderPageBanner()
    {
        $this->togglePayLaterConfiguration('setOrderPageBanner');
    }

    /**
     * AJAX: Toggle pay later banner on product page
     */
    public function ajaxProcessTogglePayLaterProductPageBanner()
    {
        $this->togglePayLaterConfiguration('setProductPageBanner');
    }

    /**
     * AJAX: Toggle pay later banner on home page
     */
    public function ajaxProcessTogglePayLaterHomePageBanner()
    {
        $this->togglePayLaterConfiguration('setHomePageBanner');
    }

    /**
     * AJAX: Toggle pay later banner on category page
     */
    public function ajaxProcessTogglePayLaterCategoryPageBanner()
    {
        $this->togglePayLaterConfiguration('setCategoryPageBanner');
    }

    /**
     * AJAX: Toggle pay later button on cart page
     */
    public function ajaxProcessTogglePayLaterCartPageButton()
    {
        $this->togglePayLaterConfiguration('setCartPageButton');
    }

    /**
     * AJAX: Toggle pay later button on order page
     */
    public function ajaxProcessTogglePayLaterOrderPageButton()
    {
        $this->togglePayLaterConfiguration('setOrderPageButton');
    }

    /**
     * AJAX: Toggle pay later button on product page
     */
    public function ajaxProcessTogglePayLaterProductPageButton()
    {
        $this->togglePayLaterConfiguration('setProductPageButton');
    }

    /**
     * @todo To be refactored with Service Container
     */
    public function ajaxProcessFetchOrder()
    {
        $isLegacy = (bool) Tools::getValue('legacy');
        $id_order = (int) Tools::getValue('id_order');

        if (empty($id_order)) {
            http_response_code(400);
            $this->ajaxDie(json_encode([
                'status' => false,
                'errors' => [
                    $this->l('No PrestaShop Order identifier received'),
                ],
            ]));
        }

        $order = new Order($id_order);

        if ($order->module !== $this->module->name) {
            http_response_code(400);
            $this->ajaxDie(json_encode([
                'status' => false,
                'errors' => [
                    strtr(
                        $this->l('This PrestaShop Order [PRESTASHOP_ORDER_ID] is not paid with PrestaShop Checkout'),
                        [
                            '[PRESTASHOP_ORDER_ID]' => $order->id,
                        ]
                    ),
                ],
            ]));
        }

        $psCheckoutCartCollection = new PrestaShopCollection('PsCheckoutCart');
        $psCheckoutCartCollection->where('id_cart', '=', (int) $order->id_cart);

        /** @var PsCheckoutCart|false $psCheckoutCart */
        $psCheckoutCart = $psCheckoutCartCollection->getFirst();

        if (false === $psCheckoutCart) {
            http_response_code(500);
            $this->ajaxDie(json_encode([
                'status' => false,
                'errors' => [
                    strtr(
                        $this->l('Unable to find PayPal Order associated to this PrestaShop Order [PRESTASHOP_ORDER_ID]'),
                        [
                            '[PRESTASHOP_ORDER_ID]' => $order->id,
                        ]
                    ),
                ],
            ]));
        }

        /** @var \PrestaShop\Module\PrestashopCheckout\PayPal\PayPalOrderProvider $paypalOrderProvider */
        $paypalOrderProvider = $this->module->getService('ps_checkout.paypal.provider.order');

        $paypalOrder = $paypalOrderProvider->getById($psCheckoutCart->paypal_order);

        if (empty($paypalOrder)) {
            http_response_code(500);
            $this->ajaxDie(json_encode([
                'status' => false,
                'errors' => [
                    strtr(
                        $this->l('Unable to fetch PayPal Order [PAYPAL_ORDER_ID]'),
                        [
                            '[PAYPAL_ORDER_ID]' => $psCheckoutCart->paypal_order,
                        ]
                    ),
                ],
            ]));
        }

        /** @var \PrestaShop\Module\PrestashopCheckout\FundingSource\FundingSourceTranslationProvider $fundingSourceTranslationProvider */
        $fundingSourceTranslationProvider = $this->module->getService('ps_checkout.funding_source.translation');
        $presenter = new OrderPresenter($this->module, $paypalOrder);

        $this->context->smarty->assign([
            'moduleName' => $this->module->displayName,
            'orderPayPal' => $presenter->present(),
            'orderPayPalBaseUrl' => $this->context->link->getAdminLink('AdminAjaxPrestashopCheckout'),
            'moduleLogoUri' => $this->module->getPathUri() . 'logo.png',
            'orderPaymentDisplayName' => $fundingSourceTranslationProvider->getPaymentMethodName($psCheckoutCart->paypal_funding),
            'orderPaymentLogoUri' => $this->module->getPathUri() . 'views/img/' . $psCheckoutCart->paypal_funding . '.svg',
        ]);

        $this->ajaxDie(json_encode([
            'status' => true,
            'content' => $isLegacy
                ? $this->context->smarty->fetch($this->module->getLocalPath() . 'views/templates/admin/ajaxPayPalOrderLegacy.tpl')
                : $this->context->smarty->fetch($this->module->getLocalPath() . 'views/templates/admin/ajaxPayPalOrder.tpl'),
        ]));
    }

    /**
     * @todo To be refactored with Service Container
     */
    public function ajaxProcessRefundOrder()
    {
        $orderPayPalId = Tools::getValue('orderPayPalRefundOrder');
        $transactionPayPalId = Tools::getValue('orderPayPalRefundTransaction');
        $amount = Tools::getValue('orderPayPalRefundAmount');
        $currency = Tools::getValue('orderPayPalRefundCurrency');

        if (empty($orderPayPalId) || false === Validate::isGenericName($orderPayPalId)) {
            http_response_code(400);
            $this->ajaxDie(json_encode([
                'status' => false,
                'errors' => [
                    $this->l('PayPal Order is invalid.', 'translations'),
                ],
            ]));
        }

        if (empty($transactionPayPalId) || false === Validate::isGenericName($transactionPayPalId)) {
            http_response_code(400);
            $this->ajaxDie(json_encode([
                'status' => false,
                'errors' => [
                    $this->l('PayPal Transaction is invalid.', 'translations'),
                ],
            ]));
        }

        if (empty($amount) || false === Validate::isPrice($amount) || $amount <= 0) {
            http_response_code(400);
            $this->ajaxDie(json_encode([
                'status' => false,
                'errors' => [
                    $this->l('PayPal refund amount is invalid.', 'translations'),
                ],
            ]));
        }

        if (empty($currency) || false === in_array($currency, ['AUD', 'BRL', 'CAD', 'CZK', 'DKK', 'EUR', 'HKD', 'HUF', 'INR', 'ILS', 'JPY', 'MYR', 'MXN', 'TWD', 'NZD', 'NOK', 'PHP', 'PLN', 'GBP', 'RUB', 'SGD', 'SEK', 'CHF', 'THB', 'USD'])) {
            // https://developer.paypal.com/docs/api/reference/currency-codes/
            http_response_code(400);
            $this->ajaxDie(json_encode([
                'status' => false,
                'errors' => [
                    $this->l('PayPal refund currency is invalid.', 'translations'),
                ],
            ]));
        }

        /** @var \PrestaShop\Module\PrestashopCheckout\PayPal\PayPalConfiguration $configurationPayPal */
        $configurationPayPal = $this->module->getService('ps_checkout.paypal.configuration');

        $response = (new PrestaShop\Module\PrestashopCheckout\Api\Payment\Order($this->context->link))->refund([
            'orderId' => $orderPayPalId,
            'captureId' => $transactionPayPalId,
            'payee' => [
                'merchant_id' => $configurationPayPal->getMerchantId(),
            ],
            'amount' => [
                'currency_code' => $currency,
                'value' => $amount,
            ],
            'note_to_payer' => 'Refund by '
                . Configuration::get(
                    'PS_SHOP_NAME',
                    null,
                    null,
                    (int) Context::getContext()->shop->id
                ),
        ]);

        if (isset($response['httpCode']) && $response['httpCode'] === 200) {
            /** @var CacheInterface $paypalOrderCache */
            $paypalOrderCache = $this->module->getService('ps_checkout.cache.paypal.order');
            if ($paypalOrderCache->has($orderPayPalId)) {
                $paypalOrderCache->delete($orderPayPalId);
            }

            $this->ajaxDie(json_encode([
                'status' => true,
                'content' => $this->l('Refund has been processed by PayPal.', 'translations'),
            ]));
        } else {
            http_response_code(isset($response['httpCode']) ? (int) $response['httpCode'] : 500);
            $this->ajaxDie(json_encode([
                'status' => false,
                'errors' => [
                    $this->l('Refund cannot be processed by PayPal.', 'translations'),
                ],
            ]));
        }
    }

    /**
     * @todo To be improved in v2.0.0
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
            $this->ajaxDie(json_encode([
                'status' => false,
                'errors' => [
                    'Logger level is invalid',
                ],
            ]));
        }

        if (false === (bool) Configuration::updateGlobalValue(LoggerFactory::PS_CHECKOUT_LOGGER_LEVEL, $level)) {
            http_response_code(500);
            $this->ajaxDie(json_encode([
                'status' => false,
                'errors' => [
                    'Unable to save logger level in PrestaShop Configuration',
                ],
            ]));
        }

        $this->ajaxDie(json_encode([
            'status' => true,
            'content' => [
                'level' => $level,
            ],
        ]));
    }

    /**
     * @todo To be improved in v2.0.0
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
            $this->ajaxDie(json_encode([
                'status' => false,
                'errors' => [
                    'Logger http format is invalid',
                ],
            ]));
        }

        if (false === (bool) Configuration::updateGlobalValue(LoggerFactory::PS_CHECKOUT_LOGGER_HTTP_FORMAT, $format)) {
            $this->ajaxDie(json_encode([
                'status' => false,
                'errors' => [
                    'Unable to save logger http format in PrestaShop Configuration',
                ],
            ]));
        }

        $this->ajaxDie(json_encode([
            'status' => true,
            'content' => [
                'httpFormat' => $format,
            ],
        ]));
    }

    /**
     * @todo To be improved in v2.0.0
     */
    public function ajaxProcessUpdateLoggerHttp()
    {
        $isEnabled = (bool) Tools::getValue('isEnabled');

        if (false === (bool) Configuration::updateGlobalValue(LoggerFactory::PS_CHECKOUT_LOGGER_HTTP, (int) $isEnabled)) {
            http_response_code(500);
            $this->ajaxDie(json_encode([
                'status' => false,
                'errors' => [
                    'Unable to save logger http in PrestaShop Configuration',
                ],
            ]));
        }

        $this->ajaxDie(json_encode([
            'status' => true,
            'content' => [
                'isEnabled' => (int) $isEnabled,
            ],
        ]));
    }

    /**
     * @todo To be improved in v2.0.0
     */
    public function ajaxProcessUpdateLoggerMaxFiles()
    {
        $maxFiles = (int) Tools::getValue('maxFiles');

        if ($maxFiles < 0 || $maxFiles > 30) {
            http_response_code(400);
            $this->ajaxDie(json_encode([
                'status' => false,
                'errors' => [
                    'Logger max files is invalid',
                ],
            ]));
        }

        if (false === (bool) Configuration::updateGlobalValue(LoggerFactory::PS_CHECKOUT_LOGGER_MAX_FILES, $maxFiles)) {
            http_response_code(500);
            $this->ajaxDie(json_encode([
                'status' => false,
                'errors' => [
                    'Unable to save logger max files in PrestaShop Configuration',
                ],
            ]));
        }

        $this->ajaxDie(json_encode([
            'status' => true,
            'content' => [
                'maxFiles' => $maxFiles,
            ],
        ]));
    }

    /**
     * AJAX: Get logs files
     */
    public function ajaxProcessGetLogFiles()
    {
        /** @var LoggerFileFinder $loggerFileFinder */
        $loggerFileFinder = $this->module->getService('ps_checkout.logger.file.finder');

        header('Content-type: application/json');
        $this->ajaxDie(json_encode($loggerFileFinder->getLogFileNames()));
    }

    /**
     * AJAX: Read a log file
     */
    public function ajaxProcessGetLogs()
    {
        header('Content-type: application/json');

        $filename = Tools::getValue('file');
        $offset = (int) Tools::getValue('offset');
        $limit = (int) Tools::getValue('limit');

        if (empty($filename) || false === Validate::isFileName($filename)) {
            http_response_code(400);
            $this->ajaxDie(json_encode([
                'status' => false,
                'errors' => [
                    'Filename is invalid.',
                ],
            ]));
        }

        /** @var LoggerDirectory $loggerDirectory */
        $loggerDirectory = $this->module->getService('ps_checkout.logger.directory');
        /** @var LoggerFileReader $loggerFileReader */
        $loggerFileReader = $this->module->getService('ps_checkout.logger.file.reader');
        $fileData = [];

        try {
            $fileData = $loggerFileReader->read(
                new SplFileObject($loggerDirectory->getPath() . $filename),
                $offset,
                $limit
            );
        } catch (Exception $exception) {
            http_response_code(500);
            $this->ajaxDie(json_encode([
                'status' => false,
                'errors' => [
                    $exception->getMessage(),
                ],
            ]));
        }

        $this->ajaxDie(json_encode([
            'status' => true,
            'file' => $fileData['filename'],
            'offset' => $fileData['offset'],
            'limit' => $fileData['limit'],
            'currentOffset' => $fileData['currentOffset'],
            'eof' => (int) $fileData['eof'],
            'lines' => $fileData['lines'],
        ]));
    }

    /**
     * AJAX: Save PayPal button configuration
     */
    public function ajaxProcessSavePaypalButtonConfiguration()
    {
        /** @var PrestaShop\Module\PrestashopCheckout\PayPal\PayPalConfiguration $paypalConfiguration */
        $paypalConfiguration = $this->module->getService('ps_checkout.paypal.configuration');
        $paypalConfiguration->setButtonConfiguration(json_decode(Tools::getValue('configuration')));

        $this->ajaxDie(json_encode(true));
    }

    /**
     * AJAX: Get or refresh token for CDN application
     */
    public function ajaxProcessGetOrRefreshToken()
    {
        /** @var \PrestaShop\Module\PrestashopCheckout\Repository\PsAccountRepository $psAccountRepository */
        $psAccountRepository = $this->module->getService('ps_checkout.repository.prestashop.account');

        try {
            $token = $psAccountRepository->getIdToken();

            $this->ajaxDie(json_encode([
                'status' => true,
                'token' => $token,
            ], JSON_PRETTY_PRINT));
        } catch (\Exception $exception) {
            http_response_code($exception->getCode());

            $this->ajaxDie(json_encode([
                'status' => false,
                'error' => $exception->getMessage(),
            ], JSON_PRETTY_PRINT));
        }
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
    protected function isAnonymousAllowed()
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function display()
    {
        if ($this->errors) {
            http_response_code(400);
            $this->ajaxDie(json_encode([
                'status' => false,
                'errors' => $this->errors,
            ]));
        }

        parent::display();
    }

    private function togglePayLaterConfiguration($method)
    {
        /** @var \PrestaShop\Module\PrestashopCheckout\PayPal\PayPalPayLaterConfiguration $payLaterConfiguration */
        $payLaterConfiguration = $this->module->getService('ps_checkout.pay_later.configuration');
        $payLaterConfiguration->$method(Tools::getValue('status') ? true : false);

        $this->ajaxDie(json_encode(true));
    }

    public function ajaxProcessUpsertSecretToken()
    {
        /** @var \PrestaShop\Module\PrestashopCheckout\Webhook\WebhookSecretTokenService $webhookSecretTokenService */
        $webhookSecretTokenService = $this->module->getService('ps_checkout.webhook.service.secret_token');

        $secret = (string) Tools::getValue('body');

        $response = [];

        try {
            $status = $webhookSecretTokenService->upsertSecretToken($secret);
        } catch (Exception $exception) {
            $status = false;
            $response['errors'] = $exception->getMessage();
        }

        http_response_code($status ? 204 : 500);
        $response['status'] = $status;
        $this->ajaxDie(json_encode($response));
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
}
