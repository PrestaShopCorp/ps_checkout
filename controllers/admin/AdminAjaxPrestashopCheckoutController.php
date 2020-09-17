<?php
/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License 3.0 (AFL-3.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 */
use Monolog\Logger;
use PrestaShop\Module\PrestashopCheckout\Api\Payment\Onboarding;
use PrestaShop\Module\PrestashopCheckout\Api\Psx\Onboarding as PsxOnboarding;
use PrestaShop\Module\PrestashopCheckout\Logger\LoggerDirectory;
use PrestaShop\Module\PrestashopCheckout\Logger\LoggerFactory;
use PrestaShop\Module\PrestashopCheckout\Logger\LoggerFileFinder;
use PrestaShop\Module\PrestashopCheckout\Logger\LoggerFileReader;
use PrestaShop\Module\PrestashopCheckout\PaypalOrder;
use PrestaShop\Module\PrestashopCheckout\Presenter\Order\OrderPendingPresenter;
use PrestaShop\Module\PrestashopCheckout\Presenter\Order\OrderPresenter;
use PrestaShop\Module\PrestashopCheckout\Presenter\Store\Modules\PaypalModule;
use PrestaShop\Module\PrestashopCheckout\Presenter\Transaction\TransactionPresenter;
use PrestaShop\Module\PrestashopCheckout\PsxData\PsxDataPrepare;
use PrestaShop\Module\PrestashopCheckout\PsxData\PsxDataValidation;
use PrestaShop\Module\PrestashopCheckout\Repository\PsAccountRepository;
use PrestaShop\Module\PrestashopCheckout\Settings\RoundingSettings;

class AdminAjaxPrestashopCheckoutController extends ModuleAdminController
{
    /**
     * @var Ps_checkout
     */
    public $module;

    /**
     * AJAX: Update payment method order
     */
    public function ajaxProcessUpdatePaymentMethodsOrder()
    {
        $this->module->getService('ps_checkout.paypal.configuration')->setPaymentMethodsOrder(Tools::getValue('paymentMethods'));
    }

    /**
     * AJAX: Update the capture mode (CAPTURE or AUTHORIZE)
     */
    public function ajaxProcessUpdateCaptureMode()
    {
        $this->module->getService('ps_checkout.paypal.configuration')->setIntent(Tools::getValue('captureMode'));
    }

    /**
     * AJAX: Update payment mode (LIVE or SANDBOX)
     */
    public function ajaxProcessUpdatePaymentMode()
    {
        $this->module->getService('ps_checkout.paypal.configuration')->setPaymentMode(Tools::getValue('paymentMode'));
    }

    /**
     * AJAX: Confirm PS Live Step Banner closed
     */
    public function ajaxProcessLiveStepConfirmed()
    {
        $this->module->getService('ps_checkout.step.live')->confirmed(true);

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
        $this->module->getService('ps_checkout.persistent.configuration')->resetPsAccount();

        $this->ajaxDie(json_encode(true));
    }

    /**
     * AJAX: Logout Paypal account
     */
    public function ajaxProcessLogOutPaypalAccount()
    {
        $this->module->getService('ps_checkout.persistent.configuration')->resetPayPalAccount();

        // we reset the Live Step banner
        $this->module->getService('ps_checkout.step.live')->confirmed(false);

        $this->ajaxDie(json_encode(true));
    }

    /**
     * AJAX: SignIn firebase account
     */
    public function ajaxProcessSignIn()
    {
        $response = $this->module->getService('ps_checkout.api.firebase.auth.factory')
            ->signIn(Tools::getValue('email'), Tools::getValue('password'));

        $this->ajaxDie(json_encode($response));
    }

    /**
     * AJAX: SignUp firebase account
     */
    public function ajaxProcessSignUp()
    {
        $response = $this->module->getService('ps_checkout.api.firebase.auth.factory')
            ->signUp(Tools::getValue('email'), Tools::getValue('password'));

        $this->ajaxDie(json_encode($response));
    }

    /**
     * AJAX: Send email to reset firebase password
     */
    public function ajaxProcessSendPasswordResetEmail()
    {
        $response = $this->module->getService('ps_checkout.api.firebase.auth.factory')
            ->resetPassword(Tools::getValue('email'));

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
            $this->ajaxDie(json_encode($errors));
        }

        // Save form in database
        if (false === $this->savePsxForm($psxForm)) {
            $this->ajaxDie(json_encode(['Cannot save in database.']));
        }

        $response = (new PsxOnboarding())->setOnboardingMerchant(array_filter($psxForm));

        $this->ajaxDie(json_encode($response));
    }

    /**
     * AJAX: Update paypal account status
     */
    public function ajaxProcessRefreshPaypalAccountStatus()
    {
        $paypalAccount = new PrestaShop\Module\PrestashopCheckout\Repository\PaypalAccountRepository();
        $psAccount = new PrestaShop\Module\PrestashopCheckout\Repository\PsAccountRepository();

        // update merchant status only if the merchant onBoarding is completed
        if ($paypalAccount->onBoardingIsCompleted()
            && $psAccount->onBoardingIsCompleted()
        ) {
            (new PrestaShop\Module\PrestashopCheckout\Updater\PaypalAccountUpdater($paypalAccount->getOnboardedAccount()))->update();
        }

        $this->ajaxDie(
            json_encode((new PaypalModule())->present())
        );
    }

    /**
     * AJAX: Retrieve the onboarding paypal link
     */
    public function ajaxProcessGetOnboardingLink()
    {
        // Generate a new onboarding link to lin a new merchant
        $this->ajaxDie(
            json_encode((new Onboarding($this->context->link))->getOnboardingLink())
        );
    }

    /**
     * AJAX: Retrieve Reporting informations
     */
    public function ajaxProcessGetReportingDatas()
    {
        $this->ajaxDie(
            json_encode([
                'orders' => (new OrderPendingPresenter())->present(),
                'transactions' => (new TransactionPresenter())->present(),
                'countAllCheckoutTransactions' => (int) Db::getInstance()->getValue('
                    SELECT COUNT(op.id_order_payment)
                    FROM `' . _DB_PREFIX_ . 'order_payment` op
                    INNER JOIN `' . _DB_PREFIX_ . 'orders` o ON (o.reference = op.order_reference)
                    WHERE op.payment_method = "Prestashop Checkout"
                    AND o.id_shop = ' . (int) Context::getContext()->shop->id
                ),
            ])
        );
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
        $psAccount = (new PsAccountRepository())->getOnboardedAccount();
        $psAccount->setPsxForm(json_encode($form));

        return $this->module->getService('ps_checkout.persistent.configuration')->savePsAccount($psAccount);
    }

    /**
     * AJAX: Toggle card hosted fields availability
     */
    public function ajaxProcessToggleCardPaymentAvailability()
    {
        $this->module->getService('ps_checkout.paypal.configuration')
            ->setCardPaymentEnabled(Tools::getValue('status') ? 1 : 0);

        (new PrestaShop\Module\PrestashopCheckout\Api\Payment\Shop(Context::getContext()->link))->updateSettings();
    }

    /**
     * AJAX: Toggle express checkout on order page
     */
    public function ajaxProcessToggleECOrderPage()
    {
        $this->module->getService('ps_checkout.express_checkout.configuration')
            ->setOrderPage(Tools::getValue('status') ? 1 : 0);

        (new PrestaShop\Module\PrestashopCheckout\Api\Payment\Shop(Context::getContext()->link))->updateSettings();
    }

    /**
     * AJAX: Toggle express checkout on checkout page
     */
    public function ajaxProcessToggleECCheckoutPage()
    {
        $this->module->getService('ps_checkout.express_checkout.configuration')
            ->setCheckoutPage(Tools::getValue('status') ? 1 : 0);

        (new PrestaShop\Module\PrestashopCheckout\Api\Payment\Shop(Context::getContext()->link))->updateSettings();
    }

    /**
     * AJAX: Toggle express checkout on product page
     */
    public function ajaxProcessToggleECProductPage()
    {
        $this->module->getService('ps_checkout.express_checkout.configuration')
            ->setProductPage(Tools::getValue('status') ? 1 : 0);

        (new PrestaShop\Module\PrestashopCheckout\Api\Payment\Shop(Context::getContext()->link))->updateSettings();
    }

    /**
     * @todo To be refactored with Service Container
     */
    public function ajaxProcessFetchOrder()
    {
        $isLegacy = (bool) Tools::getValue('legacy');
        $id_order = (int) Tools::getValue('id_order');

        if (empty($id_order)) {
            $this->ajaxDie(json_encode([
                'status' => false,
                'errors' => [
                    $this->l('No PrestaShop Order identifier received'),
                ],
            ]));
        }

        $order = new Order($id_order);

        if ($order->module !== $this->module->name) {
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

        $paypalOrderId = (new OrderMatrice())->getOrderPaypalFromPrestashop($order->id);

        if (empty($paypalOrderId)) {
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

        $orderPayPal = new PaypalOrder($paypalOrderId);

        if (false === $orderPayPal->isLoaded()) {
            $this->ajaxDie(json_encode([
                'status' => false,
                'errors' => [
                    strtr(
                        $this->l('Unable to fetch PayPal Order [PAYPAL_ORDER_ID]'),
                        [
                            '[PAYPAL_ORDER_ID]' => $paypalOrderId,
                        ]
                    ),
                ],
            ]));
        }

        $presenter = new OrderPresenter($this->module, $orderPayPal->getOrder());

        $this->context->smarty->assign([
            'moduleName' => $this->module->displayName,
            'orderPayPal' => $presenter->present(),
            'orderPayPalBaseUrl' => $this->context->link->getAdminLink('AdminAjaxPrestashopCheckout'),
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
            $this->ajaxDie(json_encode([
                'status' => false,
                'errors' => [
                    $this->l('PayPal Order is invalid.', 'translations'),
                ],
            ]));
        }

        if (empty($transactionPayPalId) || false === Validate::isGenericName($transactionPayPalId)) {
            $this->ajaxDie(json_encode([
                'status' => false,
                'errors' => [
                    $this->l('PayPal Transaction is invalid.', 'translations'),
                ],
            ]));
        }

        if (empty($amount) || false === Validate::isPrice($amount) || $amount <= 0) {
            $this->ajaxDie(json_encode([
                'status' => false,
                'errors' => [
                    $this->l('PayPal refund amount is invalid.', 'translations'),
                ],
            ]));
        }

        if (empty($currency) || false === in_array($currency, ['AUD', 'BRL', 'CAD', 'CZK', 'DKK', 'EUR', 'HKD', 'HUF', 'INR', 'ILS', 'JPY', 'MYR', 'MXN', 'TWD', 'NZD', 'NOK', 'PHP', 'PLN', 'GBP', 'RUB', 'SGD', 'SEK', 'CHF', 'THB', 'USD'])) {
            // https://developer.paypal.com/docs/api/reference/currency-codes/
            $this->ajaxDie(json_encode([
                'status' => false,
                'errors' => [
                    $this->l('PayPal refund currency is invalid.', 'translations'),
                ],
            ]));
        }

        $response = (new PrestaShop\Module\PrestashopCheckout\Api\Payment\Order($this->context->link))->refund([
            'orderId' => $orderPayPalId,
            'captureId' => $transactionPayPalId,
            'payee' => [
                'merchant_id' => (new PrestaShop\Module\PrestashopCheckout\Repository\PaypalAccountRepository())->getMerchantId(),
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
            $this->ajaxDie(json_encode([
                'status' => true,
                'content' => $this->l('Refund has been processed by PayPal.', 'translations'),
            ]));
        } else {
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
            $this->ajaxDie(json_encode([
                'status' => false,
                'errors' => [
                    'Logger level is invalid',
                ],
            ]));
        }

        if (false === (bool) Configuration::updateGlobalValue(LoggerFactory::PS_CHECKOUT_LOGGER_LEVEL, $level)) {
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
            $this->ajaxDie(json_encode([
                'status' => false,
                'errors' => [
                    'Logger max files is invalid',
                ],
            ]));
        }

        if (false === (bool) Configuration::updateGlobalValue(LoggerFactory::PS_CHECKOUT_LOGGER_MAX_FILES, $maxFiles)) {
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
}
