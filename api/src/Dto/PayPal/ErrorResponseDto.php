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

namespace PsCheckout\Api\Dto\PayPal;

/**
 * The error details.
 */
class ErrorResponseDto
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var string|string[]|null
     */
    private $message;

    /**
     * @var string
     */
    private $debugId;

    /**
     * @var ErrorDetails[]|null
     */
    private $details;

    /**
     * @var LinkDescription[]|null
     */
    private $links;

    /**
     * @var string|null
     */
    private $error;

    /**
     * @var string|null
     */
    private $errorDescription;

    /**
     * Returns Name.
     * The human-readable, unique name of the error.
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Sets Name.
     * The human-readable, unique name of the error.
     *
     * @required
     * @maps name
     * @return self
     */
    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string|string[]|null
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @param string|string[]|null $message
     * @return $this
     */
    public function setMessage($message): self
    {
        $this->message = $message;

        return $this;
    }

    /**
     * Returns Debug Id.
     * The PayPal internal ID. Used for correlation purposes.
     */
    public function getDebugId(): string
    {
        return $this->debugId;
    }

    /**
     * Sets Debug Id.
     * The PayPal internal ID. Used for correlation purposes.
     *
     * @required
     * @maps debug_id
     * @return self
     */
    public function setDebugId(string $debugId): self
    {
        $this->debugId = $debugId;

        return $this;
    }

    /**
     * Returns Details.
     * An array of additional details about the error.
     *
     * @return ErrorDetails[]|null
     */
    public function getDetails(): ?array
    {
        return $this->details;
    }

    /**
     * Sets Details.
     * An array of additional details about the error.
     *
     * @maps details
     *
     * @param ErrorDetails[]|null $details
     * @return self
     */
    public function setDetails(?array $details): self
    {
        $this->details = $details;

        return $this;
    }

    /**
     * Returns Links.
     * An array of request-related [HATEOAS links](/api/rest/responses/#hateoas-links).
     *
     * @return LinkDescription[]|null
     */
    public function getLinks(): ?array
    {
        return $this->links;
    }

    /**
     * Sets Links.
     * An array of request-related [HATEOAS links](/api/rest/responses/#hateoas-links).
     *
     * @maps links
     *
     * @param LinkDescription[]|null $links
     * @return self
     */
    public function setLinks(?array $links): self
    {
        $this->links = $links;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getError(): ?string
    {
        return $this->error;
    }

    public function setError(?string $error): self
    {
        $this->error = $error;

        return $this;
    }

    public function getErrorDescription(): ?string
    {
        return $this->errorDescription;
    }

    public function setErrorDescription(?string $errorDescription): self
    {
        $this->errorDescription = $errorDescription;

        return $this;
    }

    /**
     * @return string
     */
    public function extractMessage(): string
    {
        if ($this->details && isset($this->details[0]) && preg_match('/^[0-9A-Z_]+$/', $this->details[0]->getIssue()) === 1) {
            return $this->details[0]->getIssue();
        }

        if ($this->error && preg_match('/^[0-9A-Z_]+$/', $this->error) === 1) {
            return $this->error;
        }

        if ($this->message && is_array($this->message)) {
            return implode("\n", $this->message);
        }

        if ($this->message && preg_match('/^[0-9A-Z_]+$/', $this->message) === 1) {
            return $this->message;
        }

        if ($this->name && preg_match('/^[0-9A-Z_]+$/', $this->name) === 1) {
            return $this->name;
        }

        return '';
    }
}
