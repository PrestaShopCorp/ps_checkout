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

use PsCheckout\Core\PaymentToken\Action\DeletePaymentTokenAction;
use PsCheckout\Infrastructure\Controller\AbstractFrontController;
use PsCheckout\Infrastructure\Repository\PaymentTokenRepository;
use PsCheckout\Utility\Common\InputStreamUtility;
use Psr\Log\LoggerInterface;

/**
 * This controller receive ajax call to manage the Customer PayPal Payment Method tokens
 */
class Ps_CheckoutVaultModuleFrontController extends AbstractFrontController
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

            $customerId = $this->context->customer->isLogged() ? $this->context->customer->id : null;

            if (isset($bodyValues['action'])) {
                $action = $bodyValues['action'];

                switch ($action) {
                    case 'deleteToken':
                        $vaultId = $bodyValues['vaultId'];

                        /** @var DeletePaymentTokenAction $deletePaymentTokenAction */
                        $deletePaymentTokenAction = $this->module->getService(DeletePaymentTokenAction::class);
                        $deletePaymentTokenAction->execute($vaultId, $customerId);

                        $this->exitWithResponse([
                            'status' => true,
                            'httpCode' => 200,
                        ]);

                        break;
                    default:
                        $this->exitWithResponse([
                            'status' => false,
                            'httpCode' => 400,
                        ]);
                }
            }
            /** @var PaymentTokenRepository $paymentTokenRepository */
            $paymentTokenRepository = $this->module->getService(PaymentTokenRepository::class);
            $tokens = $paymentTokenRepository->getAllByCustomerId($customerId);

            $this->exitWithResponse([
                'status' => true,
                'httpCode' => 200,
                'body' => [
                    'customerId' => $customerId,
                    'paymentTokens' => $tokens,
                    'totalItems' => null,
                    'totalPages' => null,
                ],
            ]);
        } catch (Exception $exception) {
            /** @var LoggerInterface $logger */
            $logger = $this->module->getService(LoggerInterface::class);
            $logger->error(
                sprintf(
                    'VaultController exception %s : %s',
                    $exception->getCode(),
                    $exception->getMessage()
                )
            );

            $this->exitWithExceptionMessage($exception);
        }
    }
}
