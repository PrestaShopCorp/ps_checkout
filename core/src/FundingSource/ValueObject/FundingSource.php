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

use PsCheckout\Core\FundingSource\Constraint\FundingSourceConstraint;

class FundingSource implements \JsonSerializable
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
     * @var int
     */
    private $position;

    /**
     * @var array
     */
    private $countries;

    /**
     * @var bool
     */
    private $isEnabled;

    /**
     * @var bool
     */
    private $isToggleable;

    /**
     * @var string|null
     */
    private $customMark;

    public function __construct(
        $name,
        $label,
        $position,
        $isEnabled,
        $customMark
    ) {
        $this->name = $name;
        $this->label = $label;
        $this->position = $position;
        $this->countries = FundingSourceConstraint::getCountries($name);
        $this->isEnabled = $isEnabled;
        $this->isToggleable = !($this->name === 'paypal');
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
     * @return bool
     */
    public function getIsEnabled(): bool
    {
        return $this->isEnabled;
    }

    /**
     * @return string|null
     */
    public function getCustomMark()
    {
        return $this->customMark;
    }

    /**
     * @return array
     */
    #[\ReturnTypeWillChange]
    public function jsonSerialize(): array
    {
        $json = [
            'name' => $this->name,
            'label' => $this->label,
            'position' => $this->position,
            'countries' => $this->countries,
            'isEnabled' => $this->isEnabled,
            'isToggleable' => $this->isToggleable,
        ];

        return array_filter($json, function ($val) {
            return $val !== null;
        });
    }
}
