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
 * The error details. Required for client-side `4XX` errors.
 */
class ErrorDetails
{
    /**
     * @var string|null
     */
    private $field;

    /**
     * @var string|null
     */
    private $value;

    /**
     * @var string|null
     */
    private $location = 'body';

    /**
     * @var string
     */
    private $issue;

    /**
     * @var LinkDescription[]|null
     */
    private $links;

    /**
     * @var string|null
     */
    private $description;

    /**
     * @param string $issue
     */
    public function __construct(string $issue)
    {
        $this->issue = $issue;
    }

    /**
     * Returns Field.
     * The field that caused the error. If this field is in the body, set this value to the field's JSON
     * pointer value. Required for client-side errors.
     */
    public function getField(): ?string
    {
        return $this->field;
    }

    /**
     * Sets Field.
     * The field that caused the error. If this field is in the body, set this value to the field's JSON
     * pointer value. Required for client-side errors.
     *
     * @maps field
     * @return self
     */
    public function setField(?string $field): self
    {
        $this->field = $field;

        return $this;
    }

    /**
     * Returns Value.
     * The value of the field that caused the error.
     */
    public function getValue(): ?string
    {
        return $this->value;
    }

    /**
     * Sets Value.
     * The value of the field that caused the error.
     *
     * @maps value
     * @return self
     */
    public function setValue(?string $value): self
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Returns Location.
     * The location of the field that caused the error. Value is `body`, `path`, or `query`.
     */
    public function getLocation(): ?string
    {
        return $this->location;
    }

    /**
     * Sets Location.
     * The location of the field that caused the error. Value is `body`, `path`, or `query`.
     *
     * @maps location
     * @return self
     */
    public function setLocation(?string $location): self
    {
        $this->location = $location;

        return $this;
    }

    /**
     * Returns Issue.
     * The unique, fine-grained application-level error code.
     */
    public function getIssue(): string
    {
        return $this->issue;
    }

    /**
     * Sets Issue.
     * The unique, fine-grained application-level error code.
     *
     * @required
     * @maps issue
     * @return self
     */
    public function setIssue(string $issue): self
    {
        $this->issue = $issue;

        return $this;
    }

    /**
     * Returns Links.
     * An array of request-related [HATEOAS links](/api/rest/responses/#hateoas-links) that are either
     * relevant to the issue by providing additional information or offering potential resolutions.
     *
     * @return LinkDescription[]|null
     */
    public function getLinks(): ?array
    {
        return $this->links;
    }

    /**
     * Sets Links.
     * An array of request-related [HATEOAS links](/api/rest/responses/#hateoas-links) that are either
     * relevant to the issue by providing additional information or offering potential resolutions.
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
     * Returns Description.
     * The human-readable description for an issue. The description can change over the lifetime of an API,
     * so clients must not depend on this value.
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * Sets Description.
     * The human-readable description for an issue. The description can change over the lifetime of an API,
     * so clients must not depend on this value.
     *
     * @maps description
     * @return self
     */
    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }
}
