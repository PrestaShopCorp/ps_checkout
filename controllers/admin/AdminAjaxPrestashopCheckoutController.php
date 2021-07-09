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
use PrestaShop\Module\PrestashopCheckout\Api\Payment\Onboarding;
use PrestaShop\Module\PrestashopCheckout\Logger\LoggerDirectory;
use PrestaShop\Module\PrestashopCheckout\Logger\LoggerFactory;
use PrestaShop\Module\PrestashopCheckout\Logger\LoggerFileFinder;
use PrestaShop\Module\PrestashopCheckout\Logger\LoggerFileReader;
use PrestaShop\Module\PrestashopCheckout\Presenter\Order\OrderPresenter;
use PrestaShop\Module\PrestashopCheckout\PsxData\PsxDataPrepare;
use PrestaShop\Module\PrestashopCheckout\PsxData\PsxDataValidation;
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
     * AJAX: Update the capture mode (CAPTURE or AUTHORIZE)
     */
    public function ajaxProcessUpdateCaptureMode()
    {
        /** @var PrestaShop\Module\PrestashopCheckout\PayPal\PayPalConfiguration $paypalConfiguration */
        $paypalConfiguration = $this->module->getService('ps_checkout.paypal.configuration');
        $paypalConfiguration->setIntent(Tools::getValue('captureMode'));

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
     * AJAX: Logout ps account
     */
    public function ajaxProcessLogOutPsAccount()
    {
        /** @var \PrestaShop\Module\PrestashopCheckout\PersistentConfiguration $persistentConfiguration */
        $persistentConfiguration = $this->module->getService('ps_checkout.persistent.configuration');
        $persistentConfiguration->resetPsAccount();

        $this->ajaxDie(json_encode(true));
    }

    /**
     * AJAX: Logout Paypal account
     */
    public function ajaxProcessLogOutPaypalAccount()
    {
        /** @var \PrestaShop\Module\PrestashopCheckout\PersistentConfiguration $persistentConfiguration */
        $persistentConfiguration = $this->module->getService('ps_checkout.persistent.configuration');
        $persistentConfiguration->resetPayPalAccount();

        // we reset the Live Step banner
        /** @var \PrestaShop\Module\PrestashopCheckout\OnBoarding\Step\LiveStep $stepLive */
        $stepLive = $this->module->getService('ps_checkout.step.live');
        $stepLive->confirmed(false);
        $stepLive->viewed(false);

        // we reset the Value banner
        /** @var \PrestaShop\Module\PrestashopCheckout\OnBoarding\Step\ValueBanner $valueBanner */
        $valueBanner = $this->module->getService('ps_checkout.step.value');
        $valueBanner->closed(false);

        $this->ajaxDie(json_encode(true));
    }

    /**
     * AJAX: SignIn firebase account
     */
    public function ajaxProcessSignIn()
    {
        /** @var \PrestaShop\Module\PrestashopCheckout\Api\Firebase\AuthFactory $firebaseAuth */
        $firebaseAuth = $this->module->getService('ps_checkout.api.firebase.auth.factory');
        $response = $firebaseAuth->signIn(Tools::getValue('email'), Tools::getValue('password'));

        if (isset($response['httpCode'])) {
            http_response_code((int) $response['httpCode']);
        }

        $this->ajaxDie(json_encode($response));
    }

    /**
     * AJAX: SignUp firebase account
     */
    public function ajaxProcessSignUp()
    {
        /** @var \PrestaShop\Module\PrestashopCheckout\Api\Firebase\AuthFactory $firebaseAuth */
        $firebaseAuth = $this->module->getService('ps_checkout.api.firebase.auth.factory');
        $response = $firebaseAuth->signUp(Tools::getValue('email'), Tools::getValue('password'));

        if (isset($response['httpCode'])) {
            http_response_code((int) $response['httpCode']);
        }

        $this->ajaxDie(json_encode($response));
    }

    /**
     * AJAX: Send email to reset firebase password
     */
    public function ajaxProcessSendPasswordResetEmail()
    {
        /** @var \PrestaShop\Module\PrestashopCheckout\Api\Firebase\AuthFactory $firebaseAuth */
        $firebaseAuth = $this->module->getService('ps_checkout.api.firebase.auth.factory');
        $response = $firebaseAuth->resetPassword(Tools::getValue('email'));

        if (isset($response['httpCode'])) {
            http_response_code((int) $response['httpCode']);
        }

        $this->ajaxDie(json_encode($response));
    }

    /**
     * AJAX: Get the form Payload for PSX. Check the data and send it to PSL
     */
    public function ajaxProcessPsxSendData()
    {
        $payload = json_decode(Tools::getValue('payload'), true);
        $psxForm = (new PsxDataPrepare($payload))->prepareData();
        $errors = (new PsxDataValidation())->validateData($psxForm);

        if (!empty($errors)) {
            http_response_code(400);
            $this->ajaxDie(json_encode($errors));
        }

        /** @var PrestaShop\Module\PrestashopCheckout\Configuration\PrestaShopConfiguration $configuration */
        $configuration = $this->module->getService('ps_checkout.configuration');

        // Save form in database
        if (false === $this->savePsxForm($psxForm)) {
            http_response_code(500);
            $this->ajaxDie(json_encode(['Cannot save in database.']));
        }

        /** @var PrestaShop\Module\PrestashopCheckout\Api\Psx\Onboarding $psxOnboarding */
        $psxOnboarding = $this->module->getService('ps_checkout.api.psx.onboarding');

        $response = $psxOnboarding->setOnboardingMerchant(array_filter($psxForm));

        if (!$response['status'] && isset($response['httpCode'])) {
            http_response_code((int) $response['httpCode']);
        }

        $this->ajaxDie(json_encode($response));
    }

    /**
     * AJAX: Update paypal account status
     */
    public function ajaxProcessRefreshPaypalAccountStatus()
    {
        /** @var \PrestaShop\Module\PrestashopCheckout\Repository\PaypalAccountRepository $paypalAccount */
        $paypalAccount = $this->module->getService('ps_checkout.repository.paypal.account');
        /** @var \PrestaShop\Module\PrestashopCheckout\Repository\PsAccountRepository $psAccount */
        $psAccount = $this->module->getService('ps_checkout.repository.prestashop.account');

        // update merchant status only if the merchant onBoarding is completed
        if ($paypalAccount->onBoardingIsCompleted() && $psAccount->onBoardingIsCompleted()) {
            /** @var \PrestaShop\Module\PrestashopCheckout\Updater\PaypalAccountUpdater $updater */
            $updater = $this->module->getService('ps_checkout.updater.paypal.account');
            $updater->update($paypalAccount->getOnboardedAccount());
        }

        /** @var \PrestaShop\Module\PrestashopCheckout\Presenter\Store\Modules\PaypalModule $paypalModule */
        $paypalModule = $this->module->getService('ps_checkout.store.module.paypal');
        $this->ajaxDie(
            json_encode($paypalModule->present())
        );
    }

    /**
     * AJAX: Retrieve the onboarding paypal link
     */
    public function ajaxProcessGetOnboardingLink()
    {
        // Generate a new onboarding link to lin a new merchant
        $response = (new Onboarding($this->context->link))->getOnboardingLink();

        if (isset($response['httpCode'])) {
            http_response_code((int) $response['httpCode']);
        }

        $this->ajaxDie(
            json_encode($response)
        );
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
     * Update the psx form
     *
     * @param array $form
     *
     * @return bool
     */
    private function savePsxForm($form)
    {
        /** @var \PrestaShop\Module\PrestashopCheckout\Repository\PsAccountRepository $accountRepository */
        $accountRepository = $this->module->getService('ps_checkout.repository.prestashop.account');
        $psAccount = $accountRepository->getOnboardedAccount();
        $psAccount->setPsxForm(json_encode($form));

        /** @var \PrestaShop\Module\PrestashopCheckout\PersistentConfiguration $persistentConfiguration */
        $persistentConfiguration = $this->module->getService('ps_checkout.persistent.configuration');

        return $persistentConfiguration->savePsAccount($psAccount);
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
     * AJAX: Toggle pay in 4x on order page
     */
    public function ajaxProcessTogglePayIn4XOrderPage()
    {
        /** @var \PrestaShop\Module\PrestashopCheckout\PayPal\PayPalPayIn4XConfiguration $payIn4XConfiguration */
        $payIn4XConfiguration = $this->module->getService('ps_checkout.pay_in_4x.configuration');
        $payIn4XConfiguration->setOrderPage(Tools::getValue('status') ? true : false);
        /** @var \PrestaShop\Module\PrestashopCheckout\Repository\PsAccountRepository $psAccountRepository */
        $psAccountRepository = $this->module->getService('ps_checkout.repository.prestashop.account');

        (new PrestaShop\Module\PrestashopCheckout\Api\Payment\Shop(Context::getContext()->link))->updateSettings();
    }

    /**
     * AJAX: Toggle pay in 4x on product page
     */
    public function ajaxProcessTogglePayIn4XProductPage()
    {
        /** @var \PrestaShop\Module\PrestashopCheckout\PayPal\PayPalPayIn4XConfiguration $payIn4XConfiguration */
        $payIn4XConfiguration = $this->module->getService('ps_checkout.pay_in_4x.configuration');
        $payIn4XConfiguration->setProductPage(Tools::getValue('status') ? true : false);
        /** @var \PrestaShop\Module\PrestashopCheckout\Repository\PsAccountRepository $psAccountRepository */
        $psAccountRepository = $this->module->getService('ps_checkout.repository.prestashop.account');

        (new PrestaShop\Module\PrestashopCheckout\Api\Payment\Shop(Context::getContext()->link))->updateSettings();

        $this->ajaxDie(json_encode(true));
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

        /** @var \PrestaShop\Module\PrestashopCheckout\Repository\PaypalAccountRepository $accountRepository */
        $accountRepository = $this->module->getService('ps_checkout.repository.paypal.account');

        $response = (new PrestaShop\Module\PrestashopCheckout\Api\Payment\Order($this->context->link))->refund([
            'orderId' => $orderPayPalId,
            'captureId' => $transactionPayPalId,
            'payee' => [
                'merchant_id' => $accountRepository->getMerchantId(),
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
     * AJAX: Get merchant integration
     */
    public function ajaxProcessGetMerchantIntegration()
    {
        /** @var \PrestaShop\Module\PrestashopCheckout\Repository\PaypalAccountRepository $paypalAccount */
        $paypalAccount = $this->module->getService('ps_checkout.repository.paypal.account');

        if (!$paypalAccount->getMerchantId()) {
            $this->ajaxDie(json_encode([
                'status' => false,
                'errors' => [
                    'No merchant id found.',
                ],
            ]));
        }

        /** @var \PrestaShop\Module\PrestashopCheckout\PayPal\PayPalMerchantIntegrationProvider $payPalMerchantIntegrationProvider */
        $payPalMerchantIntegrationProvider = $this->module->getService('ps_checkout.paypal.provider.merchant_integration');

        $merchantIntegration = $payPalMerchantIntegrationProvider->getById($paypalAccount->getMerchantId());
        unset($merchantIntegration['oauth_integrations']);

        $this->ajaxDie(json_encode([
            'status' => true,
            'content' => $merchantIntegration,
        ], JSON_PRETTY_PRINT));
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
}
