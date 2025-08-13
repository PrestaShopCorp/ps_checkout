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

namespace PsCheckout\Core\FundingSource\ValueObject;

class FundingSourceToken implements \JsonSerializable
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $label;

    /**
     * @var string
     */
    private $paymentSource;

    /**
     * @var bool
     */
    private $isFavorite;

    /**
     * @var string
     */
    private $customMark;

    public function __construct($name, $label, $paymentSource, $isFavorite, $customMark)
    {
        $this->name = $name;
        $this->label = $label;
        $this->paymentSource = $paymentSource;
        $this->isFavorite = $isFavorite;
        $this->customMark = $customMark;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getLabel(): string
    {
        return $this->label;
    }

    /**
     * @return string
     */
    public function getPaymentSource(): string
    {
        return $this->paymentSource;
    }

    /**
     * @return bool
     */
    public function isFavorite(): bool
    {
        return $this->isFavorite;
    }

    /**
     * @return string
     */
    public function getCustomMark(): string
    {
        return $this->customMark;
    }

    /**
     * @return array
     */
    public function jsonSerialize(): array
    {
        $json = [
            'name' => $this->getName(),
            'label' => $this->getLabel(),
            'paymentSource' => $this->getPaymentSource(),
            'isFavorite' => $this->isFavorite(),
            'customMark' => $this->getCustomMark(),
        ];

        return array_filter($json, function ($val) {
            return $val !== null;
        });
    }
}
