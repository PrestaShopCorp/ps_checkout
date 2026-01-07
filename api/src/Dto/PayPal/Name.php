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
 * The name of the party.
 */
class Name
{
    /**
     * @var string|null
     */
    private $givenName;

    /**
     * @var string|null
     */
    private $surname;

    /**
     * Returns Given Name.
     * When the party is a person, the party's given, or first, name.
     */
    public function getGivenName(): ?string
    {
        return $this->givenName;
    }

    /**
     * Sets Given Name.
     * When the party is a person, the party's given, or first, name.
     *
     * @maps given_name
     */
    public function setGivenName(?string $givenName): void
    {
        $this->givenName = $givenName;
    }

    /**
     * Returns Surname.
     * When the party is a person, the party's surname or family name. Also known as the last name.
     * Required when the party is a person. Use also to store multiple surnames including the matronymic,
     * or mother's, surname.
     */
    public function getSurname(): ?string
    {
        return $this->surname;
    }

    /**
     * Sets Surname.
     * When the party is a person, the party's surname or family name. Also known as the last name.
     * Required when the party is a person. Use also to store multiple surnames including the matronymic,
     * or mother's, surname.
     *
     * @maps surname
     */
    public function setSurname(?string $surname): void
    {
        $this->surname = $surname;
    }
}
