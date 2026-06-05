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

namespace PsCheckout\Core\Util;

use libphonenumber\PhoneNumber;
use libphonenumber\PhoneNumberType;
use libphonenumber\PhoneNumberUtil;
use Psr\Log\LoggerInterface;

class PhoneParser
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Parses and validates a raw phone string.
     * Returns the PhoneNumber on success, null on invalid number or parse failure (logs a warning).
     *
     * @param array<string, mixed> $logContext
     *
     * @return PhoneNumber|null
     */
    public function parsePhone(string $rawPhone, string $countryIso, array $logContext = []): ?PhoneNumber
    {
        $logContext['phone'] = $rawPhone;

        try {
            $phoneUtil = PhoneNumberUtil::getInstance();
            $parsed = $phoneUtil->parse($rawPhone, $countryIso);

            return $phoneUtil->isValidNumber($parsed) ? $parsed : null;
        } catch (\libphonenumber\NumberParseException $exception) {
            $this->logger->warning('Invalid phone number format.', array_merge($logContext, ['exception' => $exception]));

            return null;
        } catch (\Throwable $exception) {
            $this->logger->warning('Unexpected error formatting phone number.', array_merge($logContext, ['exception' => $exception]));

            return null;
        }
    }

    /**
     * Resolves the phone number from an address object (phone, falling back to phone_mobile),
     * then parses and validates it.
     * Returns null when no phone is present or when the number is invalid.
     *
     * @param mixed $address
     *
     * @return PhoneNumber|null
     */
    public function parseFromAddress($address, string $countryIso, ?int $cartId = null): ?PhoneNumber
    {
        $phone = !empty($address->phone)
            ? $address->phone
            : (!empty($address->phone_mobile) ? $address->phone_mobile : '');

        if (empty($phone)) {
            return null;
        }

        return $this->parsePhone($phone, $countryIso, [
            'id_cart' => $cartId,
            'address_id' => isset($address->id) ? (int) $address->id : null,
        ]);
    }

    /**
     * Maps a libphonenumber phone type to the PayPal phone_type string.
     */
    public function getPhoneType(PhoneNumber $phone): string
    {
        $type = PhoneNumberUtil::getInstance()->getNumberType($phone);

        switch ($type) {
            case PhoneNumberType::MOBILE:
                return 'MOBILE';
            case PhoneNumberType::PAGER:
                return 'PAGER';
            default:
                return 'OTHER';
        }
    }
}
