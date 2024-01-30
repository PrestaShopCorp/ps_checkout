<?php

namespace PrestaShop\Module\PrestashopCheckout\PayPal\Order\DTO;

class LinkDescription
{
    /**
     * The complete target URL. To make the related call, combine the method with this [URI Template-formatted](https://tools.ietf.org/html/rfc6570) link. For pre-processing, include the &#x60;$&#x60;, &#x60;(&#x60;, and &#x60;)&#x60; characters. The &#x60;href&#x60; is the key HATEOAS component that links a completed call with a subsequent call.
     *
     * @var string
     */
    protected $href;

    /**
     * The [link relation type](https://tools.ietf.org/html/rfc5988#section-4), which serves as an ID for a link that unambiguously describes the semantics of the link. See [Link Relations](https://www.iana.org/assignments/link-relations/link-relations.xhtml).
     *
     * @var string
     */
    protected $rel;

    /**
     * The HTTP method required to make the related call.
     *
     * @var string|null
     */
    protected $method;

    /**
     * Constructor
     * @param mixed[] $data Associated array of property values initializing the model
     */
    public function __construct(array $data = null)
    {
        $this->href = isset($data['href']) ? $data['href'] : null;
        $this->rel = isset($data['rel']) ? $data['rel'] : null;
        $this->method = isset($data['method']) ? $data['method'] : null;
    }

    /**
     * Gets href.
     *
     * @return string
     */
    public function getHref()
    {
        return $this->href;
    }

    /**
     * Sets href.
     *
     * @param string $href The complete target URL. To make the related call, combine the method with this [URI Template-formatted](https://tools.ietf.org/html/rfc6570) link. For pre-processing, include the `$`, `(`, and `)` characters. The `href` is the key HATEOAS component that links a completed call with a subsequent call.
     *
     * @return $this
     */
    public function setHref($href)
    {
        $this->href = $href;

        return $this;
    }

    /**
     * Gets rel.
     *
     * @return string
     */
    public function getRel()
    {
        return $this->rel;
    }

    /**
     * Sets rel.
     *
     * @param string $rel The [link relation type](https://tools.ietf.org/html/rfc5988#section-4), which serves as an ID for a link that unambiguously describes the semantics of the link. See [Link Relations](https://www.iana.org/assignments/link-relations/link-relations.xhtml).
     *
     * @return $this
     */
    public function setRel($rel)
    {
        $this->rel = $rel;

        return $this;
    }

    /**
     * Gets method.
     *
     * @return string|null
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * Sets method.
     *
     * @param string|null $method The HTTP method required to make the related call.
     *
     * @return $this
     */
    public function setMethod($method = null)
    {
        $this->method = $method;

        return $this;
    }
}


