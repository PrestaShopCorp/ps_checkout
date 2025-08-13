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

namespace PsCheckout\Core\WebhookDispatcher\Validator;

use PsCheckout\Core\Webhook\WebhookException;
use PsCheckout\Infrastructure\Repository\PsAccountRepositoryInterface;

class WebhookShopIdValidator implements WebhookShopIdValidatorInterface
{
    /**
     * @var PsAccountRepositoryInterface
     */
    private $psAccountRepository;

    public function __construct(PsAccountRepositoryInterface $psAccountRepository)
    {
        $this->psAccountRepository = $psAccountRepository;
    }

    /**
     * {@inheritDoc}
     */
    public function validate(string $shopId): bool
    {
        try {
            // Step 1: Get the shop UUID from the repository
            $shopUuid = $this->psAccountRepository->getShopUuid();

            // Step 2: Compare the Shop-Id from headers with the shop UUID
            if ($shopId !== $shopUuid) {
                throw new WebhookException('Invalid Shop-Id', 401);
            }
        } catch (\Exception $e) {
            // Wrap any exceptions in a domain-specific exception
            throw new WebhookException('Failed to validate shop context: ' . $e->getMessage(), 401);
        }

        return true;
    }
}
