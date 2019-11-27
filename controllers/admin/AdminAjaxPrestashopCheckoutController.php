<?php
/**
 * 2007-2019 PrestaShop and Contributors
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
 * @copyright 2007-2019 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 * International Registered Trademark & Property of PrestaShop SA
 */
use PrestaShop\Module\PrestashopCheckout\Api\Firebase\Auth;
use PrestaShop\Module\PrestashopCheckout\Api\Payment\Onboarding;
use PrestaShop\Module\PrestashopCheckout\Api\Psx\Onboarding as PsxOnboarding;
use PrestaShop\Module\PrestashopCheckout\Entity\PsAccount;
use PrestaShop\Module\PrestashopCheckout\PersistentConfiguration;
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
        Configuration::updateValue('PS_CHECKOUT_PAYMENT_METHODS_ORDER', Tools::getValue('paymentMethods'));
    }

    /**
     * AJAX: Update the capture mode (CAPTURE or AUTHORIZE)
     */
    public function ajaxProcessUpdateCaptureMode()
    {
        Configuration::updateValue('PS_CHECKOUT_INTENT', Tools::getValue('captureMode'));
    }

    /**
     * AJAX: Update payment mode (LIVE or SANDBOX)
     */
    public function ajaxProcessUpdatePaymentMode()
    {
        Configuration::updateValue('PS_CHECKOUT_MODE', Tools::getValue('paymentMode'));
    }

    /**
     * AJAX: Change prestashop rounding settings
     *
     * PS_ROUND_TYPE need to be set to 1 (Round on each item)
     * PS_PRICE_ROUND_MODE need to be set to 2 (Round up away from zero, wh
     */
    public function ajaxProcessEditRoundingSettings()
    {
        Configuration::updateValue('PS_ROUND_TYPE', '1');
        Configuration::updateValue('PS_PRICE_ROUND_MODE', '2');

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
            $this->ajaxDie(json_encode(false));
        }

        $response = (new PsxOnboarding())->setOnboardingMerchant(array_filter($psxForm));

        if ($response) {
            $this->ajaxDie(json_encode(true));
        }

        $this->ajaxDie(json_encode(false));
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
}
