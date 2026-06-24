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
use PsCheckout\Infrastructure\Action\ProcessExpressCheckoutAction;
use PsCheckout\Infrastructure\Controller\AbstractFrontController;

/**
 * This controller receive ajax call when customer click on an express checkout button
 * We retrieve data from PayPal server-side and save it in PrestaShop to prefill order page
 * Then customer must be redirected to order page to choose shipping method
 */
class ps_checkoutExpressCheckoutModuleFrontController extends AbstractFrontController
{
    /**
     * {@inheritdoc}
     */
    public function postProcess()
    {
        $result = [];
        try {
            /** @var ProcessExpressCheckoutAction $processAction */
            $processAction = $this->module->getService(ProcessExpressCheckoutAction::class);
            $result = $processAction->execute();
        } catch (PsCheckoutException $exception) {
            if ($exception->getCode() === PsCheckoutException::PSCHECKOUT_EXPRESS_CHECKOUT_NOT_ENABLED) {
                $this->exitWithResponse(['httpCode' => 403, 'body' => 'Forbidden']);
            }
            if ($exception->getCode() === PsCheckoutException::PSCHECKOUT_EXPRESS_CHECKOUT_INVALID_PAYLOAD) {
                $this->exitWithResponse(['httpCode' => 400, 'body' => 'Payload invalid']);
            }
            $this->exitWithExceptionMessage($exception);
        } catch (Exception $exception) {
            $this->exitWithExceptionMessage($exception);
        } catch (Throwable $exception) {
            $this->exitWithExceptionMessage(new PsCheckoutException(
                'An error occurred while processing the express checkout.',
                PsCheckoutException::UNKNOWN,
                $exception
            ));
        }

        $this->exitWithResponse([
            'status' => true,
            'httpCode' => 200,
            'body' => $result,
            'exceptionCode' => null,
            'exceptionMessage' => null,
        ]);
    }
}
