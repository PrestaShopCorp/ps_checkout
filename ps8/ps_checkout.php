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

use Prestashop\ModuleLibMboInstaller\DependencyBuilder;
use PrestaShop\PrestaShop\Core\Payment\PaymentOption;
use PrestaShop\PsAccountsInstaller\Installer\Facade\PsAccounts;
use PrestaShop\PsAccountsInstaller\Installer\Presenter\InstallerPresenter;
use PsCheckout\Core\PayPal\Order\Provider\PayPalOrderProvider;
use PsCheckout\Core\PayPal\ShippingTracking\Action\AddTrackingAction;
use PsCheckout\Core\PayPal\ShippingTracking\Action\ProcessExternalShipmentAction;
use PsCheckout\Core\Settings\Configuration\DefaultConfiguration;
use PsCheckout\Core\Settings\Configuration\PayPalCodeConfiguration;
use PsCheckout\Core\Settings\Configuration\PayPalConfiguration;
use PsCheckout\Core\Settings\Configuration\PayPalSdkConfiguration;
use PsCheckout\Infrastructure\Adapter\Configuration;
use PsCheckout\Infrastructure\Adapter\Link;
use PsCheckout\Infrastructure\Adapter\ShopContext;
use PsCheckout\Infrastructure\Bootstrap\Install\Installer;
use PsCheckout\Infrastructure\Environment\Env;
use PsCheckout\Infrastructure\Repository\ConfigurationRepository;
use PsCheckout\Infrastructure\Repository\CountryRepository;
use PsCheckout\Infrastructure\Repository\CurrencyRepository;
use PsCheckout\Infrastructure\Repository\FundingSourceRepository;
use PsCheckout\Infrastructure\Repository\PayPalOrderRepository;
use PsCheckout\Infrastructure\Validator\FrontControllerValidator;
use PsCheckout\Infrastructure\Validator\MerchantValidator;
use PsCheckout\Module\Presentation\Translator;
use PsCheckout\Presentation\Presenter\FundingSource\FundingSourcePresenter;
use PsCheckout\Presentation\Presenter\FundingSource\FundingSourceTokenPresenter;
use PsCheckout\Presentation\Presenter\FundingSource\FundingSourceTranslationProvider;
use PsCheckout\Presentation\Presenter\OrderSummary\OrderSummaryPresenter;
use PsCheckout\Presentation\Presenter\Settings\Admin\AdminSettingsPresenter;
use PsCheckout\Presentation\Presenter\Settings\Front\FrontSettingsPresenter;
use PsCheckout\Utility\Common\ArrayUtility;
use Psr\Log\LoggerInterface;

require_once __DIR__ . '/vendor/autoload.php';

class Ps_Checkout extends PaymentModule
{
    /**
     * @var PrestaShop\ModuleLibServiceContainer\DependencyInjection\ServiceContainer
     */
    private $serviceContainer;

    /**
     * @var bool|null
     */
    private static $merchantIsValid;

    /**
     * @var array|null
     */
    private static $currencyIsAllowed;

    const HOOK_LIST = [
        'actionAdminControllerSetMedia',
        'actionFrontControllerSetMedia',
        'actionObjectProductInCartDeleteAfter',
        'actionCartUpdateQuantityBefore',
        'actionObjectShopAddAfter',
        'actionObjectShopDeleteAfter',
        'actionObjectOrderPaymentAddAfter',
        'actionObjectOrderPaymentUpdateAfter',
        'actionObjectOrderCarrierUpdateAfter',
        'actionGetOrderShipments',
        'paymentOptions',
        'displayPaymentTop',
        'displayPaymentByBinaries',
        'displayOrderConfirmation',
        'displayPaymentReturn',
        'displayOrderDetail',
        'displayInvoiceLegalFreeText',
        'displayAdminAfterHeader',
        'displayAdminOrderMainBottom',
        'moduleRoutes',
    ];

    public $tabs = [
        [
            'class_name' => 'AdminAjaxPrestashopCheckout',
            'visible' => false,
        ],
    ];

    public function __construct()
    {
        $this->name = 'ps_checkout';
        $this->tab = 'payments_gateways';
        $this->version = '8.5.0.2';
        $this->author = 'PrestaShop';

        parent::__construct();

        $this->displayName = $this->trans('PrestaShop Checkout');
        $this->description = $this->trans('Provide the most commonly used payment methods to your customers in this all-in-one module, and manage all your sales in a centralized interface.');
        $this->module_key = '82bc76354cfef947e06f1cc78f5efe2e';
        $this->ps_versions_compliancy = ['min' => '8.0.0', 'max' => '8.999.999'];
    }

    public function install(): bool
    {
        /** @var Installer $installer */
        $installer = $this->getService(Installer::class);

        /** @var ShopContext $shopContext */
        $shopContext = $this->getService(ShopContext::class);

        $currentShopContext = $shopContext->getCurrent();
        $shopContext->setAllShopContext();

        $result = parent::install() && $installer->init() && $this->registerHook(self::HOOK_LIST);

        $this->updatePosition(\Hook::getIdByName('paymentOptions'), false, 1);

        $shopContext->setContext($currentShopContext);

        return $result;
    }

    public function uninstall(): bool
    {
        /* @var Configuration $configuration */
        $configuration = $this->getService(Configuration::class);

        foreach (array_keys(DefaultConfiguration::DEFAULT_CONFIGURATION_VALUES) as $name) {
            $configuration->deleteByName($name);
        }

        return parent::uninstall();
    }

    public function isUsingNewTranslationSystem(): bool
    {
        return true;
    }

    public function getContent()
    {
        try {
            /** @var PsAccounts $psAccountsFacade */
            $psAccountsFacade = $this->getService(PsAccounts::class);
            /** @var InstallerPresenter $psAccountsPresenter */
            $psAccountsPresenter = $psAccountsFacade->getPsAccountsPresenter();
            $contextPsAccounts = $psAccountsPresenter->present();
        } catch (Exception $exception) {
            $contextPsAccounts = [];
            $this->getService(LoggerInterface::class)->error(
                'Failed to get PsAccounts context',
                [
                    'exception' => get_class($exception),
                    'exceptionCode' => $exception->getCode(),
                    'exceptionMessage' => $exception->getMessage(),
                ]
            );
        }

        /** @var AdminSettingsPresenter $settingsPresenter */
        $settingsPresenter = $this->getService(AdminSettingsPresenter::class);

        Media::addJsDef([
            'store' => $settingsPresenter->present(),
            'contextPsAccounts' => $contextPsAccounts,
        ]);

        /** @var Env $env */
        $env = $this->getService(Env::class);
        $boSdkUrl = $env->getEnv('CHECKOUT_BO_SDK_URL');

        if (substr($boSdkUrl, -3) !== '.js') {
            $boSdkVersion = $env->getEnv('CHECKOUT_BO_SDK_VERSION');

            $boSdkUrl = $boSdkUrl . $boSdkVersion . PayPalSdkConfiguration::SDK_BO_ENDPOINT;
        }

        $this->context->controller->addJS($boSdkUrl, false);
        $isShopContext = !(Shop::isFeatureActive() && Shop::getContext() !== Shop::CONTEXT_SHOP);
        $requiredDependencies = [];
        $hasRequiredDependencies = true;

        if ($isShopContext) {
            try {
                $mboInstaller = new DependencyBuilder($this);
                $requiredDependencies = $mboInstaller->handleDependencies();
                $hasRequiredDependencies = $mboInstaller->areDependenciesMet();
            } catch (Exception $exception) {
                $this->getService(LoggerInterface::class)->error(
                    'Failed to get required dependencies',
                    [
                        'exception' => get_class($exception),
                        'exceptionCode' => $exception->getCode(),
                        'exceptionMessage' => $exception->getMessage(),
                    ]
                );
            }
        }

        $this->context->smarty->assign([
            'requiredDependencies' => $requiredDependencies,
            'hasRequiredDependencies' => $hasRequiredDependencies,
        ]);

        return $this->display(__FILE__, 'views/templates/admin/configuration.tpl');
    }

    /**
     * Load asset on the back office
     */
    public function hookActionAdminControllerSetMedia()
    {
        switch (Tools::getValue('controller')) {
            case 'AdminModules':
                if ($this->name === Tools::getValue('configure')) {
                    $this->context->controller->addCss(
                        $this->_path . 'views/css/adminModules.css?version=' . $this->version,
                        'all',
                        null,
                        false
                    );
                }

                break;
            case 'AdminPayment':
                $this->context->controller->addCss(
                    $this->_path . 'views/css/adminAfterHeader.css?version=' . $this->version,
                    'all',
                    null,
                    false
                );

                break;
            case 'AdminCountries':
            case 'AdminCurrencies':
                $this->context->controller->addCss(
                    $this->_path . 'views/css/adminIncompatibleBanner.css?version=' . $this->version,
                    'all',
                    null,
                    false
                );

                break;
            case 'AdminOrders':
                $this->context->controller->addJS(
                    $this->getPathUri() . 'views/js/adminOrderView.js?version=' . $this->version,
                    false
                );
                $this->context->controller->addCss(
                    $this->_path . 'views/css/adminOrderView.css?version=' . $this->version,
                    'all',
                    null,
                    false
                );

                break;
        }
    }

    /**
     * Load asset on the front office
     */
    public function hookActionFrontControllerSetMedia()
    {
        if (!$this->merchantIsValid()) {
            return;
        }

        $controller = (string) Tools::getValue('controller');

        if (empty($controller) && isset($this->context->controller->php_self)) {
            $controller = $this->context->controller->php_self;
        }

        /** @var FrontControllerValidator $frontControllerValidator */
        $frontControllerValidator = $this->getService(FrontControllerValidator::class);

        if ($frontControllerValidator->shouldLoadFrontCss($controller)) {
            $this->context->controller->registerStylesheet(
                'ps-checkout-css-paymentOptions',
                $this->getPathUri() . 'views/css/payments.css?version=' . $this->version,
                [
                    'server' => 'remote',
                ]
            );
        }

        if (!$frontControllerValidator->shouldLoadFrontJS($controller)) {
            return;
        }

        /** @var FrontSettingsPresenter $settingsPresenter */
        $settingsPresenter = $this->getService(FrontSettingsPresenter::class);

        Media::addJsDef($settingsPresenter->present());
        Media::addJsDef([
            $this->name . 'RenderPaymentMethodLogos' => $frontControllerValidator->shouldDisplayFundingLogo($controller),
        ]);
        /** @var Env $env */
        $env = $this->getService(Env::class);
        $foSdkUrl = $env->getEnv('CHECKOUT_FO_SDK_URL');

        if (substr($foSdkUrl, -3) !== '.js') {
            $foSdkVersion = $env->getEnv('CHECKOUT_FO_SDK_VERSION');

            $foSdkUrl = $foSdkUrl . $foSdkVersion . PayPalSdkConfiguration::SDK_FO_ENDPOINT;
        }

        $this->context->controller->registerJavascript(
            $this->name . 'Front',
            $foSdkUrl,
            [
                'position' => 'bottom',
                'priority' => 201,
                'server' => 'remote',
            ]
        );
    }

    public function hookActionObjectProductInCartDeleteAfter()
    {
        $this->hookActionCartUpdateQuantityBefore();
    }

    public function hookActionCartUpdateQuantityBefore()
    {
        if (
            !Validate::isLoadedObject($this->context->cart)
            || !$this->merchantIsValid()
        ) {
            return;
        }

        /** @var PayPalOrderRepository $payPalOrderRepository */
        $payPalOrderRepository = $this->getService(PayPalOrderRepository::class);
        $payPalOrder = $payPalOrderRepository->getOneByCartId($this->context->cart->id);

        if ($payPalOrder && $payPalOrder->isExpressCheckout() || !$this->context->cart->nbProducts()) {
            $this->context->cookie->__unset('paypalEmail');
        }
    }

    /**
     * This hook called after a new Shop is created
     *
     * @param array{cookie: Cookie, cart: Cart, altern: int, object: Shop} $params
     */
    public function hookActionObjectShopAddAfter(array $params)
    {
        /** @var Shop $shop */
        $shop = $params['object'];
        $now = date('Y-m-d H:i:s');

        /** @var ConfigurationRepository $configurationRepository */
        $configurationRepository = $this->getService(ConfigurationRepository::class);
        $configurationRepository->handleConfigurationOnShopToggle();

        foreach (DefaultConfiguration::DEFAULT_CONFIGURATION_VALUES as $name => $value) {
            Db::getInstance()->insert(
                'configuration',
                [
                    'name' => pSQL($name),
                    'value' => pSQL($value),
                    'date_add' => pSQL($now),
                    'date_upd' => pSQL($now),
                    'id_shop' => (int) $shop->id,
                    'id_shop_group' => (int) $shop->id_shop_group,
                ],
                true,
                false
            );
        }

        /** @var FundingSourceRepository $fundingSourceRepository */
        $fundingSourceRepository = $this->getService(FundingSourceRepository::class);
        $fundingSourceRepository->populateWithDefaultValues((int) $shop->id);

        $this->addCheckboxCarrierRestrictionsForModule([(int) $shop->id]);
        $this->addCheckboxCountryRestrictionsForModule([(int) $shop->id]);

        if ($this->currencies_mode === 'checkbox') {
            $this->addCheckboxCurrencyRestrictionsForModule([(int) $shop->id]);
        } elseif ($this->currencies_mode === 'radio') {
            $this->addRadioCurrencyRestrictionsForModule([(int) $shop->id]);
        }
    }

    /**
     * @param array{cookie: Cookie, cart: Cart, altern: int, object: Shop} $params
     *
     * @return void
     */
    public function hookActionObjectShopDeleteAfter(array $params)
    {
        /** @var ConfigurationRepository $configurationRepository */
        $configurationRepository = $this->getService(ConfigurationRepository::class);
        $configurationRepository->handleConfigurationOnShopToggle();
    }

    /**
     * When an OrderPayment is created we should update fields payment_method and transaction_id
     *
     * @param array{cookie: Cookie, cart: Cart, altern: int, object: OrderPayment} $params
     */
    public function hookActionObjectOrderPaymentAddAfter(array $params)
    {
        $this->hookActionObjectOrderPaymentUpdateAfter($params);
    }

    /**
     * When an OrderPayment is updated we should update fields payment_method and transaction_id
     *
     * @param array{cookie: Cookie, cart: Cart, altern: int, object: OrderPayment} $params
     */
    public function hookActionObjectOrderPaymentUpdateAfter(array $params)
    {
        if (!isset($params['object'])) {
            return;
        }

        /** @var \OrderPayment $orderPayment */
        $orderPayment = $params['object'];

        if (!Validate::isLoadedObject($orderPayment)
            || empty($orderPayment->order_reference)
            || !empty($orderPayment->transaction_id)
            || 1 !== count(OrderPayment::getByOrderReference($orderPayment->order_reference))
        ) {
            return;
        }

        /** @var Order[] $orderCollection */
        $orderCollection = Order::getByReference($orderPayment->order_reference);

        foreach ($orderCollection as $order) {
            if ($this->name !== $order->module) {
                return;
            }

            $cartId = (int) $order->id_cart;
        }

        if (empty($cartId)) {
            return;
        }

        /** @var PayPalOrderRepository $payPalOrderRepository */
        $payPalOrderRepository = $this->getService(PayPalOrderRepository::class);
        $payPalOrder = $payPalOrderRepository->getOneByCartId($cartId);

        if (!$payPalOrder) {
            return;
        }

        try {
            /** @var PayPalOrderProvider $paypalOrderProvider */
            $paypalOrderProvider = $this->getService(PayPalOrderProvider::class);
            $payPalOrderResponse = $paypalOrderProvider->getById($payPalOrder->getId());
        } catch (Exception $exception) {
            return;
        }

        if (!empty($payPalOrderResponse->getCapture())) {
            $transactionId = $payPalOrderResponse->getCapture()['id'];
        } elseif (!empty($payPalOrderResponse->getAuthorization())) {
            $transactionId = $payPalOrderResponse->getAuthorization()['id'];
        } else {
            return;
        }

        $cardNumber = '';
        $cardBrand = '';

        if (!empty($payPalOrderResponse->getCard())) {
            $cardNumber = sprintf('#### #### #### %d', $payPalOrderResponse->getCard()['last_digits']);
            $cardBrand = $payPalOrderResponse->getCard()['brand'];
        }

        /** @var FundingSourceTranslationProvider $fundingSourceTranslationProvider */
        $fundingSourceTranslationProvider = $this->getService(FundingSourceTranslationProvider::class);

        \Db::getInstance()->update(
            'order_payment',
            [
                'payment_method' => pSQL($fundingSourceTranslationProvider->getFundingSourceName($payPalOrder->getFundingSource())),
                'transaction_id' => pSQL($transactionId),
                'card_number' => pSQL($cardNumber),
                'card_brand' => pSQL($cardBrand),
            ],
            'id_order_payment = ' . (int) $orderPayment->id
        );
    }

    /**
     * Add payment option at the checkout in the front office
     *
     * @param array{cookie: Cookie, cart: Cart, altern: int} $params
     *
     * @return array
     */
    public function hookPaymentOptions(array $params): array
    {
        /** @var Cart $cart */
        $cart = $params['cart'];

        if (!Validate::isLoadedObject($cart)
            || !$this->merchantIsValid()
            || !$this->checkCurrency($cart)
        ) {
            return [];
        }

        /** @var Configuration $configuration */
        $configuration = $this->getService(Configuration::class);
        /** @var FundingSourcePresenter $fundingSourcePresenter */
        $fundingSourcePresenter = $this->getService(FundingSourcePresenter::class);
        /** @var FundingSourceTokenPresenter $fundingSourceTokenPresenter */
        $fundingSourceTokenPresenter = $this->getService(FundingSourceTokenPresenter::class);
        /** @var FundingSourceTranslationProvider $fundingSourceTranslationProvider */
        $fundingSourceTranslationProvider = $this->getService(FundingSourceTranslationProvider::class);

        $vaultingEnabled = $configuration->getBoolean(PayPalConfiguration::PS_CHECKOUT_VAULTING)
            && $this->context->customer->isLogged();

        $this->context->smarty->assign([
            'modulePath' => $this->getPathUri(),
            'vaultingEnabled' => $vaultingEnabled,
        ]);

        $vaultedPayPal = [];

        $paymentOptions = [];

        if ($vaultingEnabled) {
            foreach ($fundingSourceTokenPresenter->getFundingSourceTokens($cart->id_customer) as $fundingSource) {
                if ($fundingSource->getPaymentSource() === 'paypal') {
                    $vaultedPayPal = [
                        'paymentIdentifier' => $fundingSource->getName(),
                        'fundingSource' => $fundingSource->getPaymentSource(),
                        'isFavorite' => $fundingSource->isFavorite(),
                        'label' => $fundingSource->getLabel(),
                        'vaultId' => explode('-', $fundingSource->getName())[1],
                    ];

                    continue;
                }

                $paymentOption = new PaymentOption();
                $paymentOption->setModuleName($this->name . '-' . $fundingSource->getName());
                $paymentOption->setCallToActionText($fundingSource->getLabel());
                $paymentOption->setBinary(true);

                $this->context->smarty->assign([
                    'paymentIdentifier' => $fundingSource->getName(),
                    'fundingSource' => $fundingSource->getPaymentSource(),
                    'isFavorite' => $fundingSource->isFavorite(),
                    'label' => $fundingSource->getLabel(),
                    'vaultId' => explode('-', $fundingSource->getName())[1],
                ]);
                $paymentOption->setForm($this->context->smarty->fetch('module:' . $this->name . '/views/templates/hook/partials/vaultTokenForm.tpl'));

                $paymentOptions[] = $paymentOption;
            }
        }
        foreach ($fundingSourcePresenter->getAllActiveForSpecificShop($this->context->shop->id) as $fundingSource) {
            $paymentOption = new PaymentOption();
            $paymentOption->setModuleName($this->name . '-' . $fundingSource->getName());
            $paymentOption->setCallToActionText($fundingSourceTranslationProvider->getPaymentMethodName(
                $fundingSource->getName(),
                $fundingSource->getLabel()
            ));
            $paymentOption->setBinary(true);
            $this->context->smarty->assign('paymentIdentifier', $fundingSource->getName());

            if (
                'card' === $fundingSource->getName()
                && $configuration->getBoolean(PayPalConfiguration::PS_CHECKOUT_CARD_HOSTED_FIELDS_ENABLED)
                && in_array(
                    $configuration->get(PayPalConfiguration::PS_CHECKOUT_CARD_HOSTED_FIELDS_STATUS),
                    [
                        'SUBSCRIBED',
                        'LIMITED',
                    ],
                    true
                )
            ) {
                $paymentOption->setForm($this->context->smarty->fetch('module:' . $this->name . '/views/templates/hook/partials/cardFields.tpl'));
            } elseif ($fundingSource->getName() === 'paypal' && empty($vaultedPayPal)) {
                $paymentOption->setForm($this->context->smarty->fetch('module:' . $this->name . '/views/templates/hook/partials/vaultPaymentForm.tpl'));
            } elseif ($fundingSource->getName() === 'paypal' && $vaultedPayPal) {
                $this->context->smarty->assign($vaultedPayPal);
                $paymentOption->setForm($this->context->smarty->fetch('module:' . $this->name . '/views/templates/hook/partials/vaultTokenForm.tpl'));
            }

            $paymentOptions[] = $paymentOption;
        }

        return $paymentOptions;
    }

    /**
     * This hook display a block on top of PaymentOptions on PrestaShop 1.7
     *
     * @return string
     */
    public function hookDisplayPaymentTop(): string
    {
        if (!Validate::isLoadedObject($this->context->cart)
            || !$this->merchantIsValid()
            || !$this->checkCurrency($this->context->cart)
        ) {
            return '';
        }

        /** @var PayPalOrderRepository $payPalOrderRepository */
        $payPalOrderRepository = $this->getService(PayPalOrderRepository::class);
        $payPalOrder = $payPalOrderRepository->getOneByCartId($this->context->cart->id);

        $isExpressCheckout = $payPalOrder && $payPalOrder->isExpressCheckout();

        $this->context->smarty->assign([
            'isExpressCheckout' => $isExpressCheckout,
            'spinnerPath' => $this->getPathUri() . 'views/img/tail-spin.svg',
            'loaderTranslatedText' => $this->trans('Please wait, loading additional payment methods.', [], 'Modules.Checkout.Pscheckout.checkout'),
            'paypalLogoPath' => $this->getPathUri() . 'views/img/paypal_express.png',
            'translatedText' => $this->trans(
                'You have selected your %s PayPal account to proceed to the payment.',
                [$this->context->cookie->__get('paypalEmail') ?: ''],
                'Modules.Checkout.Pscheckout.checkout'
            ),
            'shoppingCartWarningPath' => $this->getPathUri() . 'views/img/icons/shopping-cart-warning.svg',
            'warningTranslatedText' => $this->trans('Warning', [], 'Modules.Checkout.Pscheckout.checkout'),
        ]);

        return $this->display(__FILE__, 'views/templates/hook/displayPaymentTop.tpl');
    }

    /**
     * This hook displays form generated by binaries during the checkout
     *
     * @param array{cookie: Cookie, cart: Cart, altern: int} $params
     *
     * @return string
     */
    public function hookDisplayPaymentByBinaries(array $params): string
    {
        /** @var Cart $cart */
        $cart = $params['cart'];

        if (!Validate::isLoadedObject($cart)
            || !$this->merchantIsValid()
            || !$this->checkCurrency($cart)
        ) {
            return '';
        }

        /** @var FundingSourceTokenPresenter $fundingSourceTokenPresenter */
        $fundingSourceTokenPresenter = $this->getService(FundingSourceTokenPresenter::class);
        /** @var FundingSourcePresenter $fundingSourcePresenter */
        $fundingSourcePresenter = $this->getService(FundingSourcePresenter::class);

        $paymentOptions = [];

        foreach ($fundingSourceTokenPresenter->getFundingSourceTokens($cart->id_customer) as $fundingSource) {
            $paymentOptions[] = $fundingSource->getName();
        }

        foreach ($fundingSourcePresenter->getAllActiveForSpecificShop($this->context->shop->id) as $fundingSource) {
            $paymentOptions[] = $fundingSource->getName();
        }

        $this->context->smarty->assign([
            'paymentOptions' => $paymentOptions,
        ]);

        return $this->display(__FILE__, 'views/templates/hook/displayPaymentByBinaries.tpl');
    }

    /**
     * Hook executed at the order confirmation
     *
     * @param array{cookie: Cookie, cart: Cart, altern: int, order: Order} $params
     *
     * @return string
     */
    public function hookDisplayOrderConfirmation(array $params)
    {
        if (!$this->merchantIsValid()) {
            return '';
        }

        /** @var Order $order */
        $order = $params['order'];

        if (!Validate::isLoadedObject($order) || $order->module !== $this->name) {
            return '';
        }

        /** @var OrderSummaryPresenter $orderSummaryPresenter */
        $orderSummaryPresenter = $this->getService(OrderSummaryPresenter::class);

        try {
            $templateVars = $orderSummaryPresenter->present($order);
        } catch (Exception $exception) {
            return '';
        }

        $this->context->smarty->assign($templateVars);

        return $this->display(__FILE__, 'views/templates/hook/displayOrderConfirmation.tpl');
    }

    /**
     * Display payment status on order confirmation page
     *
     * @param array{cookie: Cookie, cart: Cart, altern: int, order: Order} $params
     *
     * @return string
     */
    public function hookDisplayPaymentReturn(array $params)
    {
        if (!$this->merchantIsValid()) {
            return '';
        }

        /** @var Order $order */
        $order = $params['order'];

        if (!Validate::isLoadedObject($order) || $order->module !== $this->name) {
            return '';
        }

        /** @var OrderSummaryPresenter $orderSummaryPresenter */
        $orderSummaryPresenter = $this->getService(OrderSummaryPresenter::class);

        try {
            $templateVars = $orderSummaryPresenter->present($order);
        } catch (Exception $exception) {
            return '';
        }

        $this->context->smarty->assign($templateVars);

        return $this->display(__FILE__, 'views/templates/hook/displayPaymentReturn.tpl');
    }

    /**
     * Display payment status on order detail page
     *
     * @param array{cookie: Cookie, cart: Cart, altern: int, order: Order} $params
     *
     * @return string
     */
    public function hookDisplayOrderDetail(array $params)
    {
        if (!$this->merchantIsValid()) {
            return '';
        }

        /** @var Order $order */
        $order = $params['order'];

        if (!Validate::isLoadedObject($order) || $order->module !== $this->name) {
            return '';
        }

        /** @var OrderSummaryPresenter $orderSummaryPresenter */
        $orderSummaryPresenter = $this->getService(OrderSummaryPresenter::class);

        try {
            $templateVars = $orderSummaryPresenter->present($order);
        } catch (Exception $exception) {
            return '';
        }

        $this->context->smarty->assign($templateVars);

        return $this->display(__FILE__, 'views/templates/hook/displayOrderDetail.tpl');
    }

    /**
     * This hook allows to add PayPal OrderId and TransactionId on PDF invoice
     *
     * @param array{cookie: Cookie, cart: Cart, altern: int, order: Order} $params
     *
     * @return string HTML is not allowed in this hook
     */
    public function hookDisplayInvoiceLegalFreeText(array $params)
    {
        if (!$this->merchantIsValid()) {
            return '';
        }

        /** @var \Order $order */
        $order = $params['order'];

        if (!Validate::isLoadedObject($order) || $this->name !== $order->module) {
            // This order has not been paid with this module
            return '';
        }

        /** @var PayPalOrderRepository $payPalOrderRepository */
        $payPalOrderRepository = $this->getService(PayPalOrderRepository::class);
        $payPalOrder = $payPalOrderRepository->getOneByCartId($order->id_cart);

        if (!$payPalOrder) {
            return '';
        }

        /* @var Configuration $configuration */
        $configuration = $this->getService(Configuration::class);
        /* @var Translator $translator */
        $translator = $this->getService(Translator::class);

        $legalFreeText = $configuration->getForSpecificShop('PS_INVOICE_LEGAL_FREE_TEXT', $order->id_shop, $order->id_lang) ?: '';

        if (!empty($legalFreeText)) {
            // If a legal free text is found, we add blank lines after
            $legalFreeText .= PHP_EOL . PHP_EOL;
        }

        $legalFreeText .= $translator->trans('Payment gateway information') . PHP_EOL;
        $legalFreeText .= $translator->trans('Order identifier') . ' ' . $payPalOrder->getId() . PHP_EOL;
        $legalFreeText .= $translator->trans('Order status') . ' ' . $payPalOrder->getStatus() . PHP_EOL;

        /** @var \OrderPayment[] $orderPayments */
        $orderPayments = $order->getOrderPaymentCollection();

        foreach ($orderPayments as $orderPayment) {
            if (!empty($orderPayment->transaction_id)) {
                $legalFreeText .= $this->trans('Transaction identifier') . ' ' . $orderPayment->transaction_id . PHP_EOL;
            }
        }

        return $legalFreeText;
    }

    /**
     * Hook used to display templates under BO header
     */
    public function hookDisplayAdminAfterHeader()
    {
        /* @var Configuration $configuration */
        $configuration = $this->getService(Configuration::class);
        /* @var Link $link */
        $link = $this->getService(Link::class);

        switch (Tools::getValue('controller')) {
            case 'AdminPayment':
                $defaultCountryCode = (new Country((int) $configuration->get('PS_COUNTRY_DEFAULT')))->iso_code;

                if (in_array($defaultCountryCode, ['FR', 'IT'])
                    && Module::isEnabled($this->name)
                    && $configuration->get(PayPalConfiguration::PS_CHECKOUT_PAYPAL_ID_MERCHANT)
                ) {
                    return '';
                }

                $params = [
                    'modulePath' => $this->getPathUri(),
                    'configureLink' => $link->getAdminLink('AdminModules', true, [], ['configure' => $this->name]),
                ];
                $template = 'views/templates/hook/adminAfterHeader/promotionBlock.tpl';

                break;
            case 'AdminCountries':
                if (!$this->merchantIsValid()) {
                    return '';
                }

                /* @var CountryRepository $countryRepository */
                $countryRepository = $this->getService(CountryRepository::class);
                $moduleCountryIsoCodes = array_column($countryRepository->getModuleCountryCodes(), 'iso_code');

                $params = [
                    'codesType' => 'countries',
                    'incompatibleCodes' => ArrayUtility::findMissingKeys($moduleCountryIsoCodes, PayPalCodeConfiguration::getCountryCodes()),
                    'paypalLink' => 'https://developer.paypal.com/docs/api/reference/country-codes/#',
                    'paymentPreferencesLink' => $link->getAdminLink('AdminPaymentPreferences'),
                ];
                $template = 'views/templates/hook/adminAfterHeader/incompatibleCodes.tpl';

                break;
            case 'AdminCurrencies':
                if (!$this->merchantIsValid()) {
                    return '';
                }

                /* @var CurrencyRepository $currencyRepository */
                $currencyRepository = $this->getService(CurrencyRepository::class);
                $moduleCurrenciesIsoCodes = array_column($currencyRepository->getModuleCurrencyCodes(), 'iso_code');

                $params = [
                    'codesType' => 'currencies',
                    'incompatibleCodes' => ArrayUtility::findMissingKeys($moduleCurrenciesIsoCodes, PayPalCodeConfiguration::getCurrencyCodes()),
                    'paypalLink' => 'https://developer.paypal.com/docs/api/reference/currency-codes/#',
                    'paymentPreferencesLink' => $link->getAdminLink('AdminPaymentPreferences'),
                ];
                $template = 'views/templates/hook/adminAfterHeader/incompatibleCodes.tpl';

                break;
            default:
                return '';
        }

        $this->context->smarty->assign($params);

        return $this->display(__FILE__, $template);
    }

    /**
     * @param array{cookie: Cookie, cart: Cart, altern: int, id_order: int} $params
     *
     * @return string
     */
    public function hookDisplayAdminOrderMainBottom(array $params)
    {
        $order = new Order((int) $params['id_order']);

        if ($order->module !== $this->name) {
            return '';
        }

        $this->context->smarty->assign([
            'moduleLogoUri' => $this->getPathUri() . 'logo.png',
            'moduleName' => $this->displayName,
            'orderPrestaShopId' => $order->id,
            'orderPayPalBaseUrl' => $this->context->link->getAdminLink('AdminAjaxPrestashopCheckout'),
        ]);

        return $this->display(__FILE__, 'views/templates/hook/displayAdminOrderMainBottom.tpl');
    }

    /**
     * Hook triggered when Order Carrier has been updated.
     *
     * @param array{order: Order, customer: Customer, carrier: Carrier} $params
     *
     * @return void
     */
    public function hookActionObjectOrderCarrierUpdateAfter(array $params)
    {
        $orderCarrier = $params['object'] ?? null;

        if (!Validate::isLoadedObject($orderCarrier)) {
            return;
        }

        $order = new Order((int) $orderCarrier->id_order);
        $carrier = new Carrier((int) $orderCarrier->id_carrier);

        $this->processTrackingNumberUpdate($order, $carrier);
    }

    public function hookActionGetOrderShipments(array $params)
    {
        foreach ($params as $shipment) {
            // External shipment data comes in $params
            $order = $shipment['order'] ?? new \Order($shipment['id_order']);

            if (!Validate::isLoadedObject($order)) {
                return;
            }

            try {
                /** @var ProcessExternalShipmentAction $processExternalShipmentAction */
                $processExternalShipmentAction = $this->getService(ProcessExternalShipmentAction::class);

                // Process external shipment data (stop on error as requested)
                $processExternalShipmentAction->execute($order, $shipment);
            } catch (\Exception $exception) {
                /** @var LoggerInterface $logger */
                $logger = $this->getService(LoggerInterface::class);
                $logger->error('Failed to process external shipment data', [
                    'order_id' => $order->id ?? 'unknown',
                    'exception' => $exception->getMessage()
                ]);
            }
        }
    }

    public function hookModuleRoutes()
    {
        return [
            'ps_checkout_applepay' => [
                'rule' => '.well-known/apple-developer-merchantid-domain-association',
                'keywords' => [],
                'controller' => 'applepay',
                'params' => [
                    'fc' => 'module',
                    'module' => $this->name,
                    'action' => 'getDomainAssociation',
                ],
            ],
        ];
    }

    /**
     * Override method to add "IGNORE" in the SQL Request to prevent duplicate entry and for getting All Carriers installed
     * Add checkbox carrier restrictions for a new module.
     *
     * @see PaymentModuleCore
     *
     * @param array $shopsIds List of Shop identifier
     *
     * @return bool
     */
    public function addCheckboxCarrierRestrictionsForModule(array $shopsIds = []): bool
    {
        $shopsIds = empty($shopsIds) ? Shop::getShops(true, null, true) : $shopsIds;
        $carriersList = Carrier::getCarriers((int) Context::getContext()->language->id, false, false, false, null, Carrier::ALL_CARRIERS);
        $carriersIds = array_column($carriersList, 'id_reference');

        $dataToInsert = [];

        foreach ($shopsIds as $shopId) {
            foreach ($carriersIds as $carrierId) {
                $dataToInsert[] = [
                    'id_reference' => (int) $carrierId,
                    'id_shop' => (int) $shopId,
                    'id_module' => (int) $this->id,
                ];
            }
        }

        return \Db::getInstance()->insert('module_carrier', $dataToInsert, false, true, Db::INSERT_IGNORE);
    }

    /**
     * Override method to add "IGNORE" in the SQL Request to prevent duplicate entry.
     * Add checkbox country restrictions for a new module.
     * Associate with all countries allowed in geolocation management
     *
     * @see PaymentModuleCore
     *
     * @param array $shopsIds List of Shop identifier
     *
     * @return bool
     */
    public function addCheckboxCountryRestrictionsForModule(array $shopsIds = [])
    {
        parent::addCheckboxCountryRestrictionsForModule($shopsIds);

        // Then add all countries allowed in geolocation management
        /* @var Configuration $configuration */
        $configuration = $this->getService(Configuration::class);
        $db = \Db::getInstance();

        $shopsIds = empty($shopsIds) ? Shop::getShops(true, null, true) : $shopsIds;
        /** @var array $countries */
        $countries = $db->executeS('SELECT id_country, iso_code FROM ' . _DB_PREFIX_ . 'country');

        $countryIdByIso = [];

        foreach ($countries as $country) {
            $countryIdByIso[$country['iso_code']] = $country['id_country'];
        }

        $dataToInsert = [];

        foreach ($shopsIds as $shopId) {
            // Get countries allowed in geolocation management for this shop
            $activeCountries = $configuration->getForSpecificShop('PS_ALLOWED_COUNTRIES', $shopId);
            $explodedCountries = explode(';', $activeCountries);

            foreach ($explodedCountries as $isoCodeCountry) {
                if (isset($countryIdByIso[$isoCodeCountry])) {
                    $dataToInsert[] = [
                        'id_country' => (int) $countryIdByIso[$isoCodeCountry],
                        'id_shop' => (int) $shopId,
                        'id_module' => (int) $this->id,
                    ];
                }
            }
        }

        return $db->insert('module_country', $dataToInsert, false, true, Db::INSERT_IGNORE);
    }

    /**
     * @param string $serviceName
     *
     * @return object|null
     */
    public function getService(string $serviceName)
    {
        if ($this->serviceContainer === null) {
            $this->serviceContainer = new PrestaShop\ModuleLibServiceContainer\DependencyInjection\ServiceContainer(
                $this->name . str_replace(['.', '-', '+'], '', $this->version),
                $this->getLocalPath()
            );
        }

        return $this->serviceContainer->getService($serviceName);
    }

    /**
     * Check if the module can process to a payment with the
     * current currency
     *
     * @param Cart $cart
     *
     * @return bool
     */
    private function checkCurrency(Cart $cart): bool
    {
        if (isset(static::$currencyIsAllowed[$cart->id_currency])) {
            return static::$currencyIsAllowed[$cart->id_currency];
        }

        $cartCurrency = Currency::getCurrencyInstance($cart->id_currency);
        $isCurrencySupported = false;

        foreach (array_keys(PayPalCodeConfiguration::getCurrencyCodes()) as $supportedCurrencyCode) {
            if (strcasecmp($supportedCurrencyCode, $cartCurrency->iso_code) === 0) {
                $isCurrencySupported = true;

                break;
            }
        }

        if (!$isCurrencySupported) {
            static::$currencyIsAllowed[$cart->id_currency] = false;

            return false;
        }

        /** @var array $moduleCurrencies */
        $moduleCurrencies = $this->getCurrency($cart->id_currency);

        if (empty($moduleCurrencies)) {
            static::$currencyIsAllowed[$cart->id_currency] = false;

            return false;
        }

        foreach ($moduleCurrencies as $moduleCurrency) {
            if ($cartCurrency->id == $moduleCurrency['id_currency']) {
                static::$currencyIsAllowed[$cart->id_currency] = true;

                return true;
            }
        }

        static::$currencyIsAllowed[$cart->id_currency] = false;

        return false;
    }

    /**
     * Check if PayPal and ps account are valid
     *
     * @return bool
     */
    private function merchantIsValid()
    {
        if (static::$merchantIsValid === null) {
            /** @var MerchantValidator $merchantValidator */
            $merchantValidator = $this->getService(MerchantValidator::class);
            static::$merchantIsValid = $merchantValidator->isValid();
        }

        return static::$merchantIsValid;
    }

    /**
     * Common logic to process tracking number update.
     *
     * @param Order|null $order
     * @param Carrier|null $carrier
     *
     * @return void
     */
    private function processTrackingNumberUpdate($order, $carrier)
    {
        try {
            if (!Validate::isLoadedObject($order) || $order->module !== $this->name) {
                return;
            }

            if ($carrier->external_module_name) {
                $carrierModule = Module::getInstanceByName($carrier->external_module_name);
                if ($carrierModule && HookCore::isModuleRegisteredOnHook($carrierModule, 'actionGetOrderShipments', $order->id_shop)) {
                    // Wait for external module to execute that hook
                    return;
                }
            }

            /** @var AddTrackingAction $addTrackingAction */
            $addTrackingAction = $this->getService(AddTrackingAction::class);
            $addTrackingAction->execute($order, $carrier);
        } catch (\Exception $exception) {
            /** @var LoggerInterface $logger */
            $logger = $this->getService(LoggerInterface::class);
            $logger->error('Failed to process tracking number update', [
                'order_id' => $order->id ?? 'unknown',
                'carrier_id' => $carrier->id ?? 'unknown',
                'exception' => $exception->getMessage()
            ]);
        }
    }
}
