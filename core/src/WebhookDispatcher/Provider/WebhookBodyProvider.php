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

namespace PsCheckout\Core\WebhookDispatcher\Provider;

use PsCheckout\Utility\Common\InputStreamUtility;

class WebhookBodyProvider implements WebhookBodyProviderInterface
{
    /**
     * @var InputStreamUtility
     */
    private $inputStreamUtility;

    public function __construct(InputStreamUtility $inputStreamUtility)
    {
        $this->inputStreamUtility = $inputStreamUtility;
    }

    /**
     * {@inheritDoc}
     */
    public function getBody(): array
    {
        $bodyContent = $this->inputStreamUtility->getBodyContent();

        if (empty($bodyContent)) {
            throw new \InvalidArgumentException('Body content is empty.');
        }

        $bodyValues = json_decode($bodyContent, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \InvalidArgumentException('Invalid JSON body: ' . json_last_error_msg());
        }

        if (empty($bodyValues)) {
            throw new \InvalidArgumentException('Body values are empty.');
        }

        return $bodyValues;
    }
}
