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

namespace PrestaShop\Module\PrestashopCheckout\PayPal\Customer\CommandHandler;

use PrestaShop\Module\PrestashopCheckout\PayPal\Customer\Command\SavePayPalCustomerCommand;
use PrestaShop\Module\PrestashopCheckout\Repository\PayPalCustomerRepository;

class SavePayPalCustomerCommandHandler
{
    /**
     * @var PayPalCustomerRepository
     */
    private $payPalCustomerRepository;

    public function __construct(PayPalCustomerRepository $payPalCustomerRepository)
    {
        $this->payPalCustomerRepository = $payPalCustomerRepository;
    }

    public function handle(SavePayPalCustomerCommand $command)
    {
        try {
            $this->payPalCustomerRepository->findPayPalCustomerIdByCustomerId($command->getCustomerId());
        } catch (\Exception $exception) {
            $this->payPalCustomerRepository->save($command->getCustomerId(), $command->getPayPalCustomerId());
        }
    }
}
