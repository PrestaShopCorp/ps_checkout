<?php
/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0  Academic Free License (AFL 3.0)
 */

namespace PrestaShop\Module\PrestashopCheckout\Sentry;

use Monolog\Processor\ProcessorInterface;
use PrestaShop\Module\PrestashopCheckout\Exception\PsCheckoutException;
use PrestaShop\Module\PrestashopCheckout\Repository\PsAccountRepository;

/**
 * Class SentryProcessor
 */
class SentryProcessor implements ProcessorInterface
{
    /**
     * @var PsAccountRepository
     */
    private $psAccount;

    /**
     * @param PsAccountRepository $psAccount
     */
    public function __construct(PsAccountRepository $psAccount)
    {
        $this->psAccount = $psAccount;
    }

    /**
     * @param array $record
     *
     * @return array
     */
    public function __invoke(array $record)
    {
        if (!isset($record['context'])) {
            $record['context'] = [];
        }

        try {
            $user = $this->psAccount->getOnboardedAccount();
            if ($user !== null) {
                $record['context']['user'] = [
                    'id' => $user->getLocalId(),
                    'email' => $user->getEmail(),
                ];
            }
        } catch (PsCheckoutException $exception) {
            // In case the ps account isn't already configure, there is no PsAccount so nothing to do
        }

        // Add various tags
        $record['context']['tags'] = [
            'platform' => 'php',
            'php_version' => phpversion(),
            'module_version' => \Ps_checkout::VERSION,
            'prestashop_version' => _PS_VERSION_,
        ];

        return $record;
    }
}
