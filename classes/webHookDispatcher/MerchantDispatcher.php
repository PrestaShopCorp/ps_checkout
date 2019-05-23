<?php
/**
* 2007-2019 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Open Software License (OSL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/osl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
* @author PrestaShop SA <contact@prestashop.com>
* @copyright 2007-2019 PrestaShop SA
* @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
* International Registered Trademark & Property of PrestaShop SA
**/

namespace PrestaShop\Module\PrestashopCheckout;

class MerchantDispatcher implements ReminderStepsValidation
{
    const PS_CHECKOUT_MERCHANT_REVOKED = 'PARTNER.CONSENT.REVOKED';
    const PS_CHECKOUT_MERCHANT_COMPLETED = 'PRESTASHOP.MERCHANT.INTEGRATIONS';
    
    /**
     * Dispatch the Event Type to manage the merchant status
     *
     * @param  string $eventType
     * @param  array $resource
     *
     * @return void
     */
    public function dispatchEventType($eventType, $resource)
    {
        if ($eventType === self::PS_CHECKOUT_MERCHANT_REVOKED ) {
            $this->setMerchantRevoked($resource);
        }

        if ($eventType === self::PS_CHECKOUT_MERCHANT_COMPLETED ) {
            $this->setMerchantCompleted($resource);
        }
    }
}
