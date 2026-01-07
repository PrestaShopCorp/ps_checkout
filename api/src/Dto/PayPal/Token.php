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
 * The tokenized payment source to fund a payment.
 */
class Token
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var string
     */
    private $type;

    /**
     * @param string $id
     * @param string $type
     */
    public function __construct(string $id, string $type)
    {
        $this->id = $id;
        $this->type = $type;
    }

    /**
     * Returns Id.
     * The PayPal-generated ID for the token.
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * Sets Id.
     * The PayPal-generated ID for the token.
     *
     * @required
     * @maps id
     * @return self
     */
    public function setId(string $id): self
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Returns Type.
     * The tokenization method that generated the ID.
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * Sets Type.
     * The tokenization method that generated the ID.
     *
     * @required
     * @maps type
     * @return self
     */
    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }
}
