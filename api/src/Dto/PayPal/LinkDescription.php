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

class LinkDescription
{
    /**
     * @var string
     */
    private $href;

    /**
     * @var string
     */
    private $rel;

    /**
     * @var string|null
     */
    private $method;

    /**
     * @param string $href
     * @param string $rel
     * @param string|null $method
     */
    public function __construct(string $href, string $rel, ?string $method)
    {
        $this->href = $href;
        $this->rel = $rel;
        $this->method = $method;
    }

    /**
     * Returns Href.
     * The complete target URL. To make the related call, combine the method with this [URI Template-
     * formatted](https://tools.ietf.org/html/rfc6570) link. For pre-processing, include the `$`, `(`, and
     * `)` characters. The `href` is the key HATEOAS component that links a completed call with a
     * subsequent call.
     */
    public function getHref(): string
    {
        return $this->href;
    }

    /**
     * Sets Href.
     * The complete target URL. To make the related call, combine the method with this [URI Template-
     * formatted](https://tools.ietf.org/html/rfc6570) link. For pre-processing, include the `$`, `(`, and
     * `)` characters. The `href` is the key HATEOAS component that links a completed call with a
     * subsequent call.
     *
     * @required
     * @maps href
     */
    public function setHref(string $href): void
    {
        $this->href = $href;
    }

    /**
     * Returns Rel.
     * The [link relation type](https://tools.ietf.org/html/rfc5988#section-4), which serves as an ID for a
     * link that unambiguously describes the semantics of the link. See [Link Relations](https://www.iana.
     * org/assignments/link-relations/link-relations.xhtml).
     */
    public function getRel(): string
    {
        return $this->rel;
    }

    /**
     * Sets Rel.
     * The [link relation type](https://tools.ietf.org/html/rfc5988#section-4), which serves as an ID for a
     * link that unambiguously describes the semantics of the link. See [Link Relations](https://www.iana.
     * org/assignments/link-relations/link-relations.xhtml).
     *
     * @required
     * @maps rel
     */
    public function setRel(string $rel): void
    {
        $this->rel = $rel;
    }

    /**
     * Returns Method.
     * The HTTP method required to make the related call.
     */
    public function getMethod(): ?string
    {
        return $this->method;
    }

    /**
     * Sets Method.
     * The HTTP method required to make the related call.
     *
     * @maps method
     */
    public function setMethod(?string $method): void
    {
        $this->method = $method;
    }
}
