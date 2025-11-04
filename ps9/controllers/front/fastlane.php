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

use PsCheckout\Core\Settings\Configuration\PayPalFastlaneConfiguration;
use PsCheckout\Infrastructure\Action\GetBillingAddressAction;
use PsCheckout\Infrastructure\Action\SaveFastlaneAddressAction;
use PsCheckout\Infrastructure\Adapter\Context;
use PsCheckout\Infrastructure\Controller\AbstractFrontController;
use PsCheckout\Utility\Common\InputStreamUtility;

class Ps_CheckoutFastlaneModuleFrontController extends AbstractFrontController
{
    public $ssl = true;

    /**
     * @var Ps_Checkout
     */
    public $module;

    /**
     * @see FrontController::postProcess()
     */
    public function postProcess()
    {
        try {
            $payload = $this->getRequestPayload();

            $action = $payload['action'] ?? '';

            switch ($action) {
                case 'saveFastlaneShippingAddress':
                    $shippingAddress = $payload['shippingAddress'];

                    if (!$shippingAddress || !is_array($shippingAddress)) {
                        $this->exitWithResponse([
                            'status' => false,
                            'httpCode' => 400,
                            'body' => [
                                'message' => 'Missing shipping address',
                            ],
                        ]);
                    }

                    if (isset($payload['toCookie']) && $payload['toCookie']) {
                        $this->handleSaveShippingAddressToCookie($shippingAddress, $payload['email']);
                    } else {
                        $this->handleSaveShippingAddress($shippingAddress);
                    }

                    $this->exitWithResponse([
                        'status' => true,
                        'httpCode' => 200,
                        'body' => [
                            'message' => 'Fastlane shipping address saved successfully',
                        ],
                    ]);

                    break;

                case 'getBillingAddress':
                    $this->handleGetBillingAddress();

                    break;

                default:
                    $this->exitWithResponse([
                        'status' => false,
                        'httpCode' => 400,
                        'body' => [
                            'message' => 'Unsupported action: ' . $action,
                        ],
                    ]);

                    break;
            }
        } catch (Exception $e) {
            $this->exitWithExceptionMessage($e);
        }
    }

    /**
     * Get and parse request payload
     *
     * @return array
     */
    private function getRequestPayload(): array
    {
        /** @var InputStreamUtility $inputStreamUtility */
        $inputStreamUtility = $this->module->getService(InputStreamUtility::class);
        $rawBody = $inputStreamUtility->getBodyContent();
        $payload = $rawBody ? json_decode($rawBody, true) : [];

        return is_array($payload) ? $payload : [];
    }

    private function handleSaveShippingAddressToCookie($shippingAddress, $email)
    {
        $this->context->cookie->{PayPalFastlaneConfiguration::PS_CHECKOUT_FASTLANE_SHIPPING_ADDRESS . $email} = json_encode($shippingAddress);
        $this->context->cookie->{PayPalFastlaneConfiguration::PS_CHECKOUT_FASTLANE_SAVED_SHIPPING_ADDRESS . $email} = true;
    }

    private function handleSaveShippingAddress($shippingAddress)
    {
        /** @var SaveFastlaneAddressAction $saveFastlaneAddressAction */
        $saveFastlaneAddressAction = $this->module->getService(SaveFastlaneAddressAction::class);
        $saveFastlaneAddressAction->execute($this->context->customer->id, $shippingAddress);

        /** @var Context $context */
        $context = $this->module->getService(Context::class);
        $context->updateCartChecksum();
    }

    /**
     * Handle getBillingAddress action
     *
     * @return void
     */
    private function handleGetBillingAddress()
    {
        /** @var GetBillingAddressAction $getBillingAddressAction */
        $getBillingAddressAction = $this->module->getService(GetBillingAddressAction::class);
        $billingAddress = $getBillingAddressAction->execute();

        $this->exitWithResponse([
            'status' => true,
            'httpCode' => 200,
            'body' => $billingAddress,
        ]);
    }
}
