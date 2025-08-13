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

use PsCheckout\Core\Customer\Action\ExpressCheckoutAction;
use PsCheckout\Core\Customer\Request\ValueObject\ExpressCheckoutRequest;
use PsCheckout\Core\PayPal\Order\Entity\PayPalOrder;
use PsCheckout\Infrastructure\Controller\AbstractFrontController;
use PsCheckout\Infrastructure\Repository\PayPalOrderRepository;
use PsCheckout\Utility\Common\InputStreamUtility;
use Psr\Log\LoggerInterface;

/**
 * This controller receive ajax call when customer click on an express checkout button
 * We retrieve data from PayPal in payload and save it in PrestaShop to prefill order page
 * Then customer must be redirected to order page to choose shipping method
 */
class ps_checkoutExpressCheckoutModuleFrontController extends AbstractFrontController
{
    /**
     * @var Ps_checkout
     */
    public $module;

    /**
     * {@inheritdoc}
     */
    public function postProcess()
    {
        $logger = $this->module->getService(LoggerInterface::class);

        try {
            // We receive data in a payload not in GET/POST
            /** @var InputStreamUtility $inputStreamUtility */
            $inputStreamUtility = $this->module->getService(InputStreamUtility::class);
            $bodyContent = $inputStreamUtility->getBodyContent();

            if (empty($bodyContent)) {
                $this->exitWithResponse([
                    'httpCode' => 400,
                    'body' => 'Payload invalid',
                ]);
            }

            $requestData = json_decode($bodyContent, true);

            if (empty($requestData)) {
                $this->exitWithResponse([
                    'httpCode' => 400,
                    'body' => 'Payload invalid',
                ]);
            }

            $expressCheckoutRequest = new ExpressCheckoutRequest($requestData);

            if (!$expressCheckoutRequest->getOrderId()) {
                $this->exitWithResponse([
                    'httpCode' => 400,
                    'body' => 'Payload invalid',
                ]);
            }

            /** @var PayPalOrderRepository $payPalOrderReposistory */
            $payPalOrderReposistory = $this->module->getService(PayPalOrderRepository::class);

            /** @var PayPalOrder|null $psCheckoutCart */
            $payPalOrder = $payPalOrderReposistory->getOneBy(['id' => $expressCheckoutRequest->getOrderId()]);

            if ($payPalOrder) {
                $payPalOrder->setFundingSource($expressCheckoutRequest->getFundingSource())
                    ->setIsExpressCheckout(true)
                    ->setIsCardFields(false);

                $payPalOrderReposistory->save($payPalOrder);
            }

            /** @var ExpressCheckoutAction $expressCheckoutAction */
            $expressCheckoutAction = $this->module->getService(ExpressCheckoutAction::class);
            $expressCheckoutAction->execute($expressCheckoutRequest);
        } catch (Exception $exception) {
            $logger->error(
                sprintf(
                    'ExpressCheckoutController - Exception %s : %s',
                    $exception->getCode(),
                    $exception->getMessage()
                ),
                [
                    'paypal_order' => $expressCheckoutRequest->getOrderId(),
                ]
            );

            $this->exitWithResponse([
                'status' => false,
                'httpCode' => 500,
                'body' => $expressCheckoutRequest->getPayload(),
                'exceptionCode' => null,
                'exceptionMessage' => null,
            ]);
        }

        $this->exitWithResponse([
            'status' => true,
            'httpCode' => 200,
            'body' => $expressCheckoutRequest->getPayload(),
            'exceptionCode' => null,
            'exceptionMessage' => null,
        ]);
    }
}
