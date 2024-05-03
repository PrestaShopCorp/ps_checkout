<?php

namespace PrestaShop\Module\PrestashopCheckout\Provider;

use Context;
use Media;
use PrestaShop\Module\PrestashopCheckout\Exception\PsCheckoutException;
use PrestaShop\Module\PrestashopCheckout\FundingSource\FundingSourceProvider;
use PrestaShop\Module\PrestashopCheckout\PayPal\PayPalConfiguration;
use PrestaShop\PrestaShop\Core\Payment\PaymentOption;
use Ps_checkout;
use SmartyException;

class PaymentOptionProvider
{
    /**
     * @var FundingSourceProvider
     */
    private $fundingSourceProvider;
    /**
     * @var PayPalConfiguration
     */
    private $payPalConfiguration;
    /**
     * @var Context
     */
    private $context;
    /**
     * @var Ps_checkout
     */
    private $module;

    public function __construct(Ps_checkout $module, FundingSourceProvider $fundingSourceProvider, PayPalConfiguration $payPalConfiguration)
    {
        $this->module = $module;
        $this->fundingSourceProvider = $fundingSourceProvider;
        $this->payPalConfiguration = $payPalConfiguration;
        $this->context = Context::getContext();
    }


    /**
     * @param int $customerId
     *
     * @return PaymentOption[]
     *
     * @throws SmartyException
     * @throws PsCheckoutException
     */
    public function getPaymentOptions($customerId) {
        $paymentOptions = [];

        $vaultingEnabled = $this->payPalConfiguration->isVaultingEnabled() && $this->context->customer->isLogged();

        $this->context->smarty->assign('lockIcon', Media::getMediaPath(_PS_MODULE_DIR_ . $this->module->name . '/views/img/icons/lock_fill.svg'));

        foreach ($this->fundingSourceProvider->getSavedTokens((int) $customerId) as $fundingSource) {
            if ($fundingSource->paymentSource === 'paypal') {
                $vaultedPayPal = [
                    'paymentIdentifier' => $fundingSource->name,
                    'fundingSource' => $fundingSource->paymentSource,
                    'isFavorite' => $fundingSource->isFavorite,
                    'label' => $fundingSource->label,
                    'vaultId' => explode('-', $fundingSource->name)[1],
                ];
                continue;
            }
            $paymentOption = new PaymentOption();
            $paymentOption->setModuleName($this->module->name . '-' . $fundingSource->name);
            $paymentOption->setCallToActionText($fundingSource->label);
            $paymentOption->setBinary(true);

            $this->context->smarty->assign([
                'paymentIdentifier' => $fundingSource->name,
                'fundingSource' => $fundingSource->paymentSource,
                'isFavorite' => $fundingSource->isFavorite,
                'label' => $fundingSource->label,
                'vaultId' => explode('-', $fundingSource->name)[1],
            ]);
            $paymentOption->setForm($this->context->smarty->fetch('module:ps_checkout/views/templates/hook/partials/vaultTokenForm.tpl'));

            $paymentOptions[] = $paymentOption;
        }

        foreach ($this->fundingSourceProvider->getAll() as $fundingSource) {
            $paymentOption = new PaymentOption();
            $paymentOption->setModuleName($this->module->name . '-' . $fundingSource->name);
            $paymentOption->setCallToActionText($fundingSource->label);
            $paymentOption->setBinary(true);
            $this->context->smarty->assign([
                'vaultingEnabled' => $vaultingEnabled,
                'paymentIdentifier' => $fundingSource->name,
            ]);

            if ('card' === $fundingSource->name && $this->payPalConfiguration->isHostedFieldsEnabled() && in_array($this->payPalConfiguration->getCardHostedFieldsStatus(), ['SUBSCRIBED', 'LIMITED'], true)) {
                $this->context->smarty->assign('modulePath', $this->module->getPathUri());
                $paymentOption->setForm($this->context->smarty->fetch('module:ps_checkout/views/templates/hook/partials/cardFields.tpl'));
            } elseif ($fundingSource->name === 'paypal' && empty($vaultedPayPal)) {
                $paymentOption->setForm($this->context->smarty->fetch('module:ps_checkout/views/templates/hook/partials/vaultPaymentForm.tpl'));
            } elseif ($fundingSource->name === 'paypal' && $vaultedPayPal) {
                $this->context->smarty->assign($vaultedPayPal);
                $paymentOption->setForm($this->context->smarty->fetch('module:ps_checkout/views/templates/hook/partials/vaultTokenForm.tpl'));
            }

            $paymentOptions[] = $paymentOption;
        }

        return $paymentOptions;
    }
}
