<?php

namespace PrestaShop\Module\PrestashopCheckout\PayPal\Customer\CommandHandler;

use PrestaShop\Module\PrestashopCheckout\Repository\PayPalCustomerRepository;
use PrestaShop\Module\PrestashopCheckout\PayPal\Customer\Command\SavePayPalCustomerCommand;

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
