<?php

namespace PrestaShop\Module\PrestashopCheckout\Validator;

use PrestaShop\Module\PrestashopCheckout\Context\PrestaShopContext;
use PrestaShop\Module\PrestashopCheckout\Repository\PaypalAccountRepository;
use PrestaShop\Module\PrestashopCheckout\Repository\PsAccountRepository;
use PrestaShop\Module\PrestashopCheckout\ShopUuidManager;

class MerchantValidator
{
    /**
     * @var PaypalAccountRepository
     */
    private $paypalAccountRepository;
    /**
     * @var PsAccountRepository
     */
    private $psAccountRepository;
    /**
     * @var PrestaShopContext
     */
    private $prestaShopContext;
    /**
     * @var ShopUuidManager
     */
    private $shopUuidManager;

    public function __construct(
        PaypalAccountRepository $paypalAccountRepository,
        PsAccountRepository $psAccountRepository,
        PrestaShopContext $prestaShopContext,
        ShopUuidManager $shopUuidManager
    ) {
        $this->paypalAccountRepository = $paypalAccountRepository;
        $this->psAccountRepository = $psAccountRepository;
        $this->prestaShopContext = $prestaShopContext;
        $this->shopUuidManager = $shopUuidManager;
    }

    public function merchantIsValid()
    {
        $shopUuid = $this->shopUuidManager->getForShop((int) $this->prestaShopContext->getShopId());

        return $this->paypalAccountRepository->onBoardingIsCompleted()
            && $this->paypalAccountRepository->paypalEmailIsValid()
            && $this->psAccountRepository->onBoardingIsCompleted()
            && $shopUuid;
    }
}
