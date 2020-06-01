<?php
/**
 * 2007-2020 PrestaShop and Contributors
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License 3.0 (AFL-3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2020 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 * International Registered Trademark & Property of PrestaShop SA
 */
use PrestaShop\Module\PrestashopCheckout\Api\Firebase\Auth;
use PrestaShop\Module\PrestashopCheckout\Api\Payment\Onboarding;
use PrestaShop\Module\PrestashopCheckout\Api\Psx\Onboarding as PsxOnboarding;
use PrestaShop\Module\PrestashopCheckout\Entity\PsAccount;
use PrestaShop\Module\PrestashopCheckout\PaypalOrder;
use PrestaShop\Module\PrestashopCheckout\PersistentConfiguration;
use PrestaShop\Module\PrestashopCheckout\Presenter\Order\OrderPendingPresenter;
use PrestaShop\Module\PrestashopCheckout\Presenter\Order\OrderPresenter;
use PrestaShop\Module\PrestashopCheckout\Presenter\Store\Modules\PaypalModule;
use PrestaShop\Module\PrestashopCheckout\Presenter\Transaction\TransactionPresenter;
use PrestaShop\Module\PrestashopCheckout\PsxData\PsxDataPrepare;
use PrestaShop\Module\PrestashopCheckout\PsxData\PsxDataValidation;
use PrestaShop\Module\PrestashopCheckout\Repository\PaypalAccountRepository;
use PrestaShop\Module\PrestashopCheckout\Repository\PsAccountRepository;

class AdminAjaxPrestashopCheckoutController extends ModuleAdminController
{
    /**
     * AJAX: Update payment method order
     */
    public function ajaxProcessUpdatePaymentMethodsOrder()
    {
        Configuration::updateValue(
            'PS_CHECKOUT_PAYMENT_METHODS_ORDER',
            Tools::getValue('paymentMethods'),
            false,
            null,
            (int) Context::getContext()->shop->id
        );
    }

    /**
     * AJAX: Update the capture mode (CAPTURE or AUTHORIZE)
     */
    public function ajaxProcessUpdateCaptureMode()
    {
        Configuration::updateValue(
            'PS_CHECKOUT_INTENT',
            Tools::getValue('captureMode'),
            false,
            null,
            (int) Context::getContext()->shop->id
        );
    }

    /**
     * AJAX: Update payment mode (LIVE or SANDBOX)
     */
    public function ajaxProcessUpdatePaymentMode()
    {
        Configuration::updateValue(
            'PS_CHECKOUT_MODE',
            Tools::getValue('paymentMode'),
            false,
            null,
            (int) Context::getContext()->shop->id
        );
    }

    /**
     * AJAX: Change prestashop rounding settings
     *
     * PS_ROUND_TYPE need to be set to 1 (Round on each item)
     * PS_PRICE_ROUND_MODE need to be set to 2 (Round up away from zero, wh
     */
    public function ajaxProcessEditRoundingSettings()
    {
        Configuration::updateValue(
            'PS_ROUND_TYPE',
            '1',
            false,
            null,
            (int) Context::getContext()->shop->id
        );
        Configuration::updateValue(
            'PS_PRICE_ROUND_MODE',
            '2',
            false,
            null,
            (int) Context::getContext()->shop->id
        );

        $this->ajaxDie(json_encode(true));
    }

    /**
     * AJAX: Logout ps account
     */
    public function ajaxProcessLogOutPsAccount()
    {
        // logout ps account
        $psAccount = (new PsAccountRepository())->getOnboardedAccount();

        $psAccount->setEmail('');
        $psAccount->setIdToken('');
        $psAccount->setLocalId('');
        $psAccount->setRefreshToken('');
        $psAccount->setPsxForm('');

        (new PersistentConfiguration())->savePsAccount($psAccount);

        $this->ajaxDie(json_encode(true));
    }

    /**
     * AJAX: Logout Paypal account
     */
    public function ajaxProcessLogOutPaypalAccount()
    {
        $paypalAccount = (new PaypalAccountRepository())->getOnboardedAccount();

        $paypalAccount->setMerchantId('');
        $paypalAccount->setEmail('');
        $paypalAccount->setEmailIsVerified('');
        $paypalAccount->setPaypalPaymentStatus('');
        $paypalAccount->setCardPaymentStatus('');

        (new PersistentConfiguration())->savePaypalAccount($paypalAccount);

        $this->ajaxDie(json_encode(true));
    }

    /**
     * AJAX: SignIn firebase account
     */
    public function ajaxProcessSignIn()
    {
        $email = Tools::getValue('email');
        $password = Tools::getValue('password');

        $firebase = new Auth();
        $response = $firebase->signInWithEmailAndPassword($email, $password);

        // if there is no error, save the account tokens in database
        if (true === $response['status']) {
            $psAccount = new PsAccount(
                $response['body']['idToken'],
                $response['body']['refreshToken'],
                $response['body']['email'],
                $response['body']['localId']
            );

            (new PersistentConfiguration())->savePsAccount($psAccount);
        }

        $this->ajaxDie(json_encode($response));
    }

    /**
     * AJAX: SignUp firebase account
     */
    public function ajaxProcessSignUp()
    {
        $email = Tools::getValue('email');
        $password = Tools::getValue('password');

        $firebase = new Auth();
        $response = $firebase->signUpWithEmailAndPassword($email, $password);

        // if there is no error, save the account tokens in database
        if (true === $response['status']) {
            $psAccount = new PsAccount(
                $response['body']['idToken'],
                $response['body']['refreshToken'],
                $response['body']['email'],
                $response['body']['localId']
            );

            (new PersistentConfiguration())->savePsAccount($psAccount);
        }

        $this->ajaxDie(json_encode($response));
    }

    /**
     * AJAX: Send email to reset firebase password
     */
    public function ajaxProcessSendPasswordResetEmail()
    {
        $email = Tools::getValue('email');

        $firebase = new Auth();
        $response = $firebase->sendPasswordResetEmail($email);

        $this->ajaxDie(json_encode($response));
    }

    /**
     * AJAX: Get the form Payload for PSX. Check the data and send it to PSL
     */
    public function ajaxProcessPsxSendData()
    {
        $payload = json_decode(\Tools::getValue('payload'), true);
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

        // update merchant status only if the merchant onboarding is completed
        if ($paypalAccount->onbardingIsCompleted()
            && $psAccount->onbardingIsCompleted()
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
                'countAllCheckoutTransactions' => (int) \Db::getInstance()->getValue('
                    SELECT COUNT(op.id_order_payment)
                    FROM `' . _DB_PREFIX_ . 'order_payment` op
                    INNER JOIN `' . _DB_PREFIX_ . 'orders` o ON (o.reference = op.order_reference)
                    WHERE op.payment_method = "Prestashop Checkout"
                    AND o.id_shop = ' . (int) \Context::getContext()->shop->id
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

        return (new PersistentConfiguration())->savePsAccount($psAccount);
    }

    /**
     * AJAX: Toggle card hosted fields availability
     */
    public function ajaxProcessToggleCardPaymentAvailability()
    {
        Configuration::updateValue(
            'PS_CHECKOUT_CARD_PAYMENT_ENABLED',
            Tools::getValue('status') ? 1 : 0,
            false,
            null,
            (int) Context::getContext()->shop->id
        );

        (new PrestaShop\Module\PrestashopCheckout\Api\Payment\Shop(\Context::getContext()->link))->updateSettings();
    }

    /**
     * AJAX: Toggle express checkout on order page
     */
    public function ajaxProcessToggleECOrderPage()
    {
        Configuration::updateValue(
            'PS_CHECKOUT_EC_ORDER_PAGE',
            Tools::getValue('status') ? 1 : 0,
            false,
            null,
            (int) Context::getContext()->shop->id
        );

        (new PrestaShop\Module\PrestashopCheckout\Api\Payment\Shop(\Context::getContext()->link))->updateSettings();
    }

    /**
     * AJAX: Toggle express checkout on checkout page
     */
    public function ajaxProcessToggleECCheckoutPage()
    {
        Configuration::updateValue(
            'PS_CHECKOUT_EC_CHECKOUT_PAGE',
            Tools::getValue('status') ? 1 : 0,
            false,
            null,
            (int) Context::getContext()->shop->id
        );

        (new PrestaShop\Module\PrestashopCheckout\Api\Payment\Shop(\Context::getContext()->link))->updateSettings();
    }

    /**
     * AJAX: Toggle express checkout on product page
     */
    public function ajaxProcessToggleECProductPage()
    {
        Configuration::updateValue(
            'PS_CHECKOUT_EC_PRODUCT_PAGE',
            Tools::getValue('status') ? 1 : 0,
            false,
            null,
            (int) Context::getContext()->shop->id
        );

        (new PrestaShop\Module\PrestashopCheckout\Api\Payment\Shop(\Context::getContext()->link))->updateSettings();
    }

    /**
     * AJAX: Toggle logs for debug
     */
    public function ajaxProcessToggleDebugLogs()
    {
        Configuration::updateValue(
            'PS_CHECKOUT_DEBUG_LOGS_ENABLED',
            Tools::getValue('status') ? 1 : 0,
            false,
            null,
            (int) Context::getContext()->shop->id
        );
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

        $paypalOrderId = (new \OrderMatrice())->getOrderPaypalFromPrestashop($order->id);

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
                . \Configuration::get(
                    'PS_SHOP_NAME',
                    null,
                    null,
                    (int) \Context::getContext()->shop->id
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
}
