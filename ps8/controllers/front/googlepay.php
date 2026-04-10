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

use PsCheckout\Core\Exception\PsCheckoutException;
use PsCheckout\Core\PayPal\GooglePay\Builder\GooglePayPaymentRequestDataBuilder;
use PsCheckout\Infrastructure\Controller\AbstractFrontController;
use PsCheckout\Utility\Common\InputStreamUtility;

/**
 * This controller receive ajax call on customer click on a payment button
 */
class Ps_CheckoutGooglepayModuleFrontController extends AbstractFrontController
{
    /**
     * @see FrontController::postProcess()
     */
    public function postProcess()
    {
        try {
            $bodyValues = [];

            /** @var InputStreamUtility $inputStreamUtility */
            $inputStreamUtility = $this->module->getService(InputStreamUtility::class);
            $bodyContent = $inputStreamUtility->getBodyContent();

            if (!empty($bodyContent)) {
                $bodyValues = json_decode($bodyContent, true);
            }

            $action = $bodyValues['action'] ?? null;

            if ($action === 'getTransactionInfo') {
                $this->getTransactionInfo();
            } else {
                $this->exitWithExceptionMessage(new Exception('Invalid request', 400));
            }
        } catch (Exception $exception) {
            $this->exitWithExceptionMessage($exception);
        } catch (Throwable $exception) {
            $this->exitWithExceptionMessage(new PsCheckoutException(
                'An error occurred while processing the Google Pay request.',
                PsCheckoutException::UNKNOWN,
                $exception
            ));
        }
    }

    /**
     * @return void
     */
    private function getTransactionInfo()
    {
        /** @var GooglePayPaymentRequestDataBuilder $googlePayPaymentRequestDataBuilder */
        $googlePayPaymentRequestDataBuilder = $this->module->getService(GooglePayPaymentRequestDataBuilder::class);
        $transactionInfo = $googlePayPaymentRequestDataBuilder->build($this->context->cart->id);

        $this->exitWithResponse([
            'httpCode' => 200,
            'body' => $transactionInfo->toArray(),
        ]);
    }
}
