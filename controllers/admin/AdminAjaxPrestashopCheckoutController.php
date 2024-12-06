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
use PrestaShop\Module\PrestashopCheckout\CommandBus\CommandBusInterface;
use PrestaShop\Module\PrestashopCheckout\Configuration\BatchConfigurationProcessor;
use PrestaShop\Module\PrestashopCheckout\Configuration\PrestaShopConfiguration;
use PrestaShop\Module\PrestashopCheckout\Exception\PayPalException;
use PrestaShop\Module\PrestashopCheckout\ExpressCheckout\ExpressCheckoutConfiguration;
use PrestaShop\Module\PrestashopCheckout\FundingSource\FundingSourceConfigurationRepository;
use PrestaShop\Module\PrestashopCheckout\FundingSource\FundingSourceTranslationProvider;
use PrestaShop\Module\PrestashopCheckout\Http\MaaslandHttpClient;
use PrestaShop\Module\PrestashopCheckout\Logger\LoggerDirectory;
use PrestaShop\Module\PrestashopCheckout\Logger\LoggerFactory;
use PrestaShop\Module\PrestashopCheckout\Logger\LoggerFileFinder;
use PrestaShop\Module\PrestashopCheckout\Logger\LoggerFileReader;
use PrestaShop\Module\PrestashopCheckout\OnBoarding\Step\LiveStep;
use PrestaShop\Module\PrestashopCheckout\OnBoarding\Step\ValueBanner;
use PrestaShop\Module\PrestashopCheckout\Order\Exception\OrderException;
use PrestaShop\Module\PrestashopCheckout\Order\State\Exception\OrderStateException;
use PrestaShop\Module\PrestashopCheckout\Order\State\OrderStateInstaller;
use PrestaShop\Module\PrestashopCheckout\Order\State\Service\OrderStateMapper;
use PrestaShop\Module\PrestashopCheckout\PayPal\ApplePay\AppleSetup;
use PrestaShop\Module\PrestashopCheckout\PayPal\ApplePay\Exception\ApplePaySetupException;
use PrestaShop\Module\PrestashopCheckout\PayPal\Mode;
use PrestaShop\Module\PrestashopCheckout\PayPal\Payment\Refund\Command\RefundPayPalCaptureCommand;
use PrestaShop\Module\PrestashopCheckout\PayPal\Payment\Refund\Exception\PayPalRefundException;
use PrestaShop\Module\PrestashopCheckout\PayPal\Payment\Refund\Exception\PayPalRefundFailedException;
use PrestaShop\Module\PrestashopCheckout\PayPal\PayPalConfiguration;
use PrestaShop\Module\PrestashopCheckout\PayPal\PayPalOrderProvider;
use PrestaShop\Module\PrestashopCheckout\PayPal\PayPalPayLaterConfiguration;
use PrestaShop\Module\PrestashopCheckout\Presenter\Order\OrderPresenter;
use PrestaShop\Module\PrestashopCheckout\Repository\PaymentTokenRepository;
use PrestaShop\Module\PrestashopCheckout\Repository\PsAccountRepository;
use PrestaShop\Module\PrestashopCheckout\Settings\RoundingSettings;
use PrestaShop\Module\PrestashopCheckout\Validator\BatchConfigurationValidator;
use PrestaShop\Module\PrestashopCheckout\Webhook\WebhookSecretTokenService;

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
        /** @var FundingSourceConfigurationRepository $fundingSourceConfigurationRepository */
        $fundingSourceConfigurationRepository = $this->module->getService(FundingSourceConfigurationRepository::class);

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
        $paypalConfiguration = $this->module->getService(PrestaShop\Module\PrestashopCheckout\PayPal\PayPalConfiguration::class);
        $paypalConfiguration->setPaymentMode(Tools::getValue('paymentMode'));

        $this->ajaxDie(json_encode(true));
    }

    /**
     * AJAX: Confirm PS Live Step Banner closed
     */
    public function ajaxProcessLiveStepConfirmed()
    {
        /** @var LiveStep $stepLive */
        $stepLive = $this->module->getService(LiveStep::class);
        $stepLive->confirmed(true);

        $this->ajaxDie(json_encode(true));
    }

    /**
     * AJAX: Confirm PS Live Step fist time
     */
    public function ajaxProcessLiveStepViewed()
    {
        /** @var LiveStep $stepLive */
        $stepLive = $this->module->getService(LiveStep::class);
        $stepLive->viewed(true);

        $this->ajaxDie(json_encode(true));
    }

    /**
     * AJAX: Confirm PS Value Banner closed
     */
    public function ajaxProcessValueBannerClosed()
    {
        /** @var ValueBanner $valueBanner */
        $valueBanner = $this->module->getService(ValueBanner::class);
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
        /** @var PayPalConfiguration $paypalConfiguration */
        $paypalConfiguration = $this->module->getService(PayPalConfiguration::class);
        $paypalConfiguration->setRoundType(RoundingSettings::ROUND_ON_EACH_ITEM);
        $paypalConfiguration->setPriceRoundMode(RoundingSettings::ROUND_UP_AWAY_FROM_ZERO);

        $this->ajaxDie(json_encode(true));
    }

    /**
     * @deprecated No more used
     */
    public function ajaxProcessGetReportingDatas()
    {
        $this->ajaxDie(
            json_encode([
                'orders' => [],
                'transactions' => [],
            ])
        );
    }

    /**
     * AJAX: Toggle payment option hosted fields availability
     */
    public function ajaxProcessTogglePaymentOptionAvailability()
    {
        $paymentOption = json_decode(Tools::getValue('paymentOption'), true);

        /** @var FundingSourceConfigurationRepository $fundingSourceConfigurationRepository */
        $fundingSourceConfigurationRepository = $this->module->getService(FundingSourceConfigurationRepository::class);

        $fundingSourceConfigurationRepository->save($paymentOption);

        $this->ajaxDie(json_encode(true));
    }

    /**
     * AJAX: Update credit card fields (Hosted fields / Smartbutton)
     */
    public function ajaxProcessUpdateCreditCardFields()
    {
        /** @var PayPalConfiguration $paypalConfiguration */
        $paypalConfiguration = $this->module->getService(PayPalConfiguration::class);

        $paypalConfiguration->setCardPaymentEnabled((bool) Tools::getValue('hostedFieldsEnabled'));

        $this->ajaxDie(json_encode(true));
    }

    /**
     * AJAX: Toggle express checkout on order page
     */
    public function ajaxProcessToggleECOrderPage()
    {
        /** @var ExpressCheckoutConfiguration $ecConfiguration */
        $ecConfiguration = $this->module->getService(ExpressCheckoutConfiguration::class);
        $ecConfiguration->setOrderPage((bool) Tools::getValue('status'));

        $this->updateExpressCheckoutSettings();

        $this->ajaxDie(json_encode(true));
    }

    /**
     * AJAX: Toggle express checkout on checkout page
     */
    public function ajaxProcessToggleECCheckoutPage()
    {
        /** @var ExpressCheckoutConfiguration $ecConfiguration */
        $ecConfiguration = $this->module->getService(ExpressCheckoutConfiguration::class);
        $ecConfiguration->setCheckoutPage(Tools::getValue('status') ? true : false);

        $this->updateExpressCheckoutSettings();

        $this->ajaxDie(json_encode(true));
    }

    /**
     * AJAX: Toggle express checkout on product page
     */
    public function ajaxProcessToggleECProductPage()
    {
        /** @var ExpressCheckoutConfiguration $ecConfiguration */
        $ecConfiguration = $this->module->getService(ExpressCheckoutConfiguration::class);
        $ecConfiguration->setProductPage(Tools::getValue('status') ? true : false);

        $this->updateExpressCheckoutSettings();

        $this->ajaxDie(json_encode(true));
    }

    /**
     * @return void
     *
     * @throws PayPalException
     */
    private function updateExpressCheckoutSettings()
    {
        /** @var PrestaShopConfiguration $configuration */
        $configuration = $this->module->getService(PrestaShopConfiguration::class);
        /** @var ExpressCheckoutConfiguration $ecConfiguration */
        $ecConfiguration = $this->module->getService(ExpressCheckoutConfiguration::class);
        /** @var MaaslandHttpClient $maaslandHttpClient */
        $maaslandHttpClient = $this->module->getService(MaaslandHttpClient::class);

        $maaslandHttpClient->updateSettings([
            'settings' => [
                'cb' => (bool) $configuration->get('PS_CHECKOUT_CARD_PAYMENT_ENABLED'),
                'express_in_product' => (bool) $ecConfiguration->isProductPageEnabled(),
                'express_in_cart' => (bool) $ecConfiguration->isOrderPageEnabled(),
                'express_in_checkout' => (bool) $ecConfiguration->isCheckoutPageEnabled(),
            ],
        ]);
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
        $psCheckoutCartCollection->orderBy('date_upd', 'ASC');

        if (!$psCheckoutCartCollection->count()) {
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

        $psCheckoutCart = null;

        foreach ($psCheckoutCartCollection->getResults() as $psCheckoutCart) {
            /** @var PsCheckoutCart $psCheckoutCart */
            if ($psCheckoutCart->getPaypalStatus() === PsCheckoutCart::STATUS_COMPLETED) {
                break;
            }
        }

        /** @var PayPalConfiguration $configurationPayPal */
        $configurationPayPal = $this->module->getService(PayPalConfiguration::class);

        if ($configurationPayPal->getPaymentMode() !== $psCheckoutCart->getEnvironment()) {
            http_response_code(422);
            $this->ajaxDie(json_encode([
                'status' => false,
                'errors' => [
                    strtr(
                        $this->l('PayPal Order [PAYPAL_ORDER_ID] is not in the same environment as PrestaShop Checkout'),
                        [
                            '[PAYPAL_ORDER_ID]' => $psCheckoutCart->paypal_order,
                        ]
                    ),
                ],
            ]));
        }

        /** @var PayPalOrderProvider $paypalOrderProvider */
        $paypalOrderProvider = $this->module->getService(PayPalOrderProvider::class);

        try {
            $paypalOrder = $paypalOrderProvider->getById($psCheckoutCart->paypal_order);
        } catch (Exception $exception) {
            $paypalOrder = [];
        }

        if ($paypalOrder === false) {
            $paypalOrder = [];
        }

        /** @var FundingSourceTranslationProvider $fundingSourceTranslationProvider */
        $fundingSourceTranslationProvider = $this->module->getService(FundingSourceTranslationProvider::class);
        $presenter = new OrderPresenter($this->module, $paypalOrder);

        $this->context->smarty->assign([
            'moduleName' => $this->module->displayName,
            'moduleUrl' => $this->context->link->getAdminLink('AdminModules', true, [], ['configure' => 'ps_checkout']),
            'orderPayPal' => $presenter->present(),
            'orderPayPalBaseUrl' => $this->context->link->getAdminLink('AdminAjaxPrestashopCheckout'),
            'moduleLogoUri' => $this->module->getPathUri() . 'logo.png',
            'orderPaymentDisplayName' => $fundingSourceTranslationProvider->getPaymentMethodName($psCheckoutCart->paypal_funding),
            'orderPaymentLogoUri' => $this->module->getPathUri() . 'views/img/' . $psCheckoutCart->paypal_funding . '.svg',
            'psCheckoutCart' => $psCheckoutCart,
            'isProductionEnv' => $psCheckoutCart->getEnvironment() === Mode::LIVE,
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
        $captureId = Tools::getValue('orderPayPalRefundTransaction');
        $amount = Tools::getValue('orderPayPalRefundAmount');
        $currency = Tools::getValue('orderPayPalRefundCurrency');

        /** @var CommandBusInterface $commandBus */
        $commandBus = $this->module->getService('ps_checkout.bus.command');

        try {
            $commandBus->handle(new RefundPayPalCaptureCommand($orderPayPalId, $captureId, $currency, $amount));
        } catch (PayPalRefundFailedException $exception) {
            $this->exitWithResponse([
                'httpCode' => $exception->getCode(),
                'status' => false,
                'errors' => [
                    $this->l('Refund cannot be processed by PayPal.', 'translations'),
                ],
            ]);
        } catch (PayPalRefundException $invalidArgumentException) {
            $error = '';
            switch ($invalidArgumentException->getCode()) {
                case PayPalRefundException::INVALID_ORDER_ID:
                    $error = $this->l('PayPal Order is invalid.', 'translations');
                    break;
                case PayPalRefundException::INVALID_TRANSACTION_ID:
                    $error = $this->l('PayPal Transaction is invalid.', 'translations');
                    break;
                case PayPalRefundException::INVALID_CURRENCY:
                    $error = $this->l('PayPal refund currency is invalid.', 'translations');
                    break;
                case PayPalRefundException::INVALID_AMOUNT:
                    $error = $this->l('PayPal refund amount is invalid.', 'translations');
                    break;
                default:
                    break;
            }
            $this->exitWithResponse([
                'httpCode' => 400,
                'status' => false,
                'errors' => [$error],
            ]);
        } catch (OrderException $exception) {
            if ($exception->getCode() === OrderException::FAILED_UPDATE_ORDER_STATUS) {
                $this->exitWithResponse([
                    'httpCode' => 200,
                    'status' => true,
                    'content' => $this->l('Refund has been processed by PayPal, but order status change or email sending failed.', 'translations'),
                ]);
            } elseif ($exception->getCode() !== OrderException::ORDER_HAS_ALREADY_THIS_STATUS) {
                $this->exitWithResponse([
                    'httpCode' => 500,
                    'status' => false,
                    'errors' => [
                        $exception->getMessage(),
                    ],
                    'error' => $exception->getMessage(),
                ]);
            }
        } catch (Exception $exception) {
            $this->exitWithResponse([
                'httpCode' => 500,
                'status' => false,
                'errors' => [
                    $this->l('Refund cannot be processed by PayPal.', 'translations'),
                ],
                'error' => $exception->getMessage(),
            ]);
        }

        $this->exitWithResponse([
            'httpCode' => 200,
            'status' => true,
            'content' => $this->l('Refund has been processed by PayPal.', 'translations'),
        ]);
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
        $loggerFileFinder = $this->module->getService(LoggerFileFinder::class);

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
        $loggerDirectory = $this->module->getService(LoggerDirectory::class);
        /** @var LoggerFileReader $loggerFileReader */
        $loggerFileReader = $this->module->getService(LoggerFileReader::class);
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
        $paypalConfiguration = $this->module->getService(PrestaShop\Module\PrestashopCheckout\PayPal\PayPalConfiguration::class);
        $paypalConfiguration->setButtonConfiguration(json_decode(Tools::getValue('configuration')));

        $this->ajaxDie(json_encode(true));
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
        /** @var PayPalPayLaterConfiguration $payLaterConfiguration */
        $payLaterConfiguration = $this->module->getService(PayPalPayLaterConfiguration::class);
        $payLaterConfiguration->$method(Tools::getValue('status') ? true : false);

        $this->ajaxDie(json_encode(true));
    }

    public function ajaxProcessUpsertSecretToken()
    {
        /** @var WebhookSecretTokenService $webhookSecretTokenService */
        $webhookSecretTokenService = $this->module->getService(WebhookSecretTokenService::class);

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
            if ($exception->getCode() === OrderStateException::INVALID_MAPPING) {
                (new OrderStateInstaller())->install();
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
        /** @var BatchConfigurationValidator $configurationValidator */
        $configurationValidator = $this->module->getService(BatchConfigurationValidator::class);
        /** @var BatchConfigurationProcessor $batchConfigurationProcessor */
        $batchConfigurationProcessor = $this->module->getService(BatchConfigurationProcessor::class);

        $configuration = json_decode(Tools::getValue('configuration'), true);
        try {
            $configurationValidator->validateAjaxBatchConfiguration($configuration);
            $batchConfigurationProcessor->saveBatchConfiguration($configuration);

            $this->exitWithResponse([
                'status' => true,
            ]);
        } catch (Exception $exception) {
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
        /** @var PaymentTokenRepository $paymentTokenRepository */
        $paymentTokenRepository = $this->module->getService(PaymentTokenRepository::class);

        /** @var PayPalConfiguration $payPalConfiguration */
        $payPalConfiguration = $this->module->getService(PayPalConfiguration::class);

        $this->exitWithResponse([
            'status' => true,
            'count' => $paymentTokenRepository->getCount(null, $payPalConfiguration->getMerchantId()),
        ]);
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

    public function ajaxProcessDownloadLogs()
    {
        $filename = Tools::getValue('file');

        if (empty($filename) || false === Validate::isFileName($filename)) {
            $this->exitWithResponse([
                'status' => false,
                'httpCode' => 400,
                'errors' => [
                    'Filename is invalid.',
                ],
            ]);
        }

        /** @var LoggerDirectory $loggerDirectory */
        $loggerDirectory = $this->module->getService(LoggerDirectory::class);

        $file = new SplFileObject($loggerDirectory->getPath() . $filename);

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

    public function ajaxProcessSetupApplePay()
    {
        /** @var AppleSetup $appleSetup */
        $appleSetup = $this->module->getService(AppleSetup::class);

        try {
            $appleSetup->setup();
        } catch (ApplePaySetupException $e) {
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
}
