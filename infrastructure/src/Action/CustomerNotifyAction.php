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

namespace PsCheckout\Infrastructure\Action;

use Cart;
use Contact;
use Customer;
use CustomerMessage;
use CustomerThread;
use Exception;
use PsCheckout\Core\PayPal\Order\Repository\PayPalOrderRepositoryInterface;
use PsCheckout\Infrastructure\Repository\OrderRepositoryInterface;
use PsCheckout\Presentation\TranslatorInterface;
use Tools;

class CustomerNotifyAction implements CustomerNotifyActionInterface
{
    /**
     * @var PayPalOrderRepositoryInterface
     */
    private $payPalOrderRepository;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var \Db
     */
    private $db;

    public function __construct(
        PayPalOrderRepositoryInterface $payPalOrderRepository,
        OrderRepositoryInterface $orderRepository,
        TranslatorInterface $translator,
        \Db $db
    ) {
        $this->payPalOrderRepository = $payPalOrderRepository;
        $this->orderRepository = $orderRepository;
        $this->translator = $translator;
        $this->db = $db;
    }

    /**
     * {@inheritDoc}
     */
    public function execute(Exception $exception, string $paypalOrderId)
    {
        $payPalOrder = $this->payPalOrderRepository->getOneBy(['id' => $paypalOrderId]);

        $order = $this->orderRepository->getOneBy(['id_cart' => $payPalOrder->getIdCart()]);

        if (!$payPalOrder || !$order) {
            return null;
        }

        $cart = new Cart($payPalOrder->getIdCart());

        $contacts = Contact::getContacts((int) $cart->id_lang);

        if (empty($contacts)) {
            return;
        }

        // Cannot use id_cart because we create a new cart to preserve current cart from customer changes
        $token = Tools::substr(
            Tools::encrypt(implode(
                '|',
                [
                    (int) $cart->id_customer,
                    (int) $cart->id_shop,
                    (int) $cart->id_lang,
                    (int) $exception->getCode(),
                    $paypalOrderId,
                    get_class($exception),
                ]
            )),
            0,
            12
        );

        $isThreadAlreadyCreated = (bool) $this->db->getValue('
            SELECT 1
            FROM ' . _DB_PREFIX_ . 'customer_thread
            WHERE id_customer = ' . (int) $cart->id_customer . '
            AND id_shop = ' . (int) $cart->id_shop . '
            AND status = "open"
            AND token = "' . pSQL($token) . '"
        ');

        // Prevent spam Customer Service on case of page refresh
        if ($isThreadAlreadyCreated) {
            return;
        }

        $message = $this->translator->trans('This message is sent automatically by module PrestaShop Checkout') . PHP_EOL . PHP_EOL;
        $message .= $this->translator->trans('A customer encountered a processing payment error :') . PHP_EOL;
        $message .= $this->translator->trans('Customer identifier:') . ' ' . (int) $cart->id_customer . PHP_EOL;
        $message .= $this->translator->trans('Cart identifier:') . ' ' . (int) $cart->id . PHP_EOL;
        $message .= $this->translator->trans('PayPal order identifier:') . ' ' . Tools::safeOutput($paypalOrderId) . PHP_EOL;
        $message .= $this->translator->trans('Exception identifier:') . ' ' . (int) $exception->getCode() . PHP_EOL;
        $message .= $this->translator->trans('Exception detail:') . ' ' . Tools::safeOutput($exception->getMessage())
            . ($exception->getPrevious() !== null ? ': ' . Tools::safeOutput($exception->getPrevious()->getMessage()) : '')
            . PHP_EOL . PHP_EOL;
        $message .= $this->translator->trans('If you need assistance, please contact our Support Team on PrestaShop Checkout configuration page on Help subtab.') . PHP_EOL;

        $customer = new Customer((int) $cart->id_customer);

        $customerThread = new CustomerThread();
        $customerThread->id_customer = (int) $cart->id_customer;
        $customerThread->id_shop = (int) $cart->id_shop;
        $customerThread->id_order = (int) $order->id;
        $customerThread->id_lang = (int) $cart->id_lang;
        $customerThread->id_contact = (int) $contacts[0]['id_contact'];
        $customerThread->email = $customer->email;
        $customerThread->status = 'open';
        $customerThread->token = $token;
        $customerThread->add();

        $customerMessage = new CustomerMessage();
        $customerMessage->id_customer_thread = $customerThread->id;
        $customerMessage->message = $message;
        $customerMessage->ip_address = (int) ip2long(Tools::getRemoteAddr());
        $customerMessage->user_agent = $_SERVER['HTTP_USER_AGENT'];
        $customerMessage->private = true;
        $customerMessage->read = false;
        $customerMessage->add();
    }
}
