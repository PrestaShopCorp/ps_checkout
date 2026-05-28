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

namespace PsCheckout\Core\WebhookDispatcher\Entity;

class WebhookEvent
{
    const STATUS_PROCESSING = 'processing';

    const STATUS_SUCCEEDED = 'succeeded';

    const STATUS_FAILED = 'failed';

    /**
     * @var string
     */
    private $id;

    /**
     * @var string
     */
    private $eventType;

    /**
     * @var string|null
     */
    private $paypalOrderId;

    /**
     * @var string
     */
    private $status;

    public function __construct(
        string $id,
        string $eventType,
        ?string $paypalOrderId,
        string $status
    ) {
        $this->id = $id;
        $this->eventType = $eventType;
        $this->paypalOrderId = $paypalOrderId;
        $this->status = $status;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getEventType(): string
    {
        return $this->eventType;
    }

    public function getPaypalOrderId(): ?string
    {
        return $this->paypalOrderId;
    }

    public function getStatus(): string
    {
        return $this->status;
    }
}
