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

namespace Tests\Unit\PsCheckout\Infrastructure\Repository;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use PsCheckout\Infrastructure\Repository\PsCheckoutAddressRepository;

class PsCheckoutAddressRepositoryTest extends TestCase
{
    /** @var \Db|MockObject */
    private $db;

    /** @var PsCheckoutAddressRepository */
    private $repository;

    protected function setUp(): void
    {
        $this->db = $this->createMock(\Db::class);
        $this->db->method('escape')->willReturnArgument(0);
        $this->repository = new PsCheckoutAddressRepository($this->db);
    }

    // -------------------------------------------------------------------------
    // getAddressIdByChecksumAndCustomer
    // -------------------------------------------------------------------------

    public function testGetAddressIdReturnsZeroWhenCustomerIdIsZero(): void
    {
        $this->db->expects($this->never())->method('getValue');

        $result = $this->repository->getAddressIdByChecksumAndCustomer('abc123', 0);

        $this->assertSame(0, $result);
    }

    public function testGetAddressIdReturnsZeroWhenNotFound(): void
    {
        $this->db->method('getValue')->willReturn(null);

        $result = $this->repository->getAddressIdByChecksumAndCustomer('abc123', 5);

        $this->assertSame(0, $result);
    }

    public function testGetAddressIdReturnsAddressIdWhenFound(): void
    {
        $this->db->method('getValue')->willReturn('42');

        $result = $this->repository->getAddressIdByChecksumAndCustomer('abc123', 5);

        $this->assertSame(42, $result);
    }

    public function testGetAddressIdQueriesDbOnce(): void
    {
        $this->db->expects($this->once())
            ->method('getValue')
            ->willReturn('10');

        $this->repository->getAddressIdByChecksumAndCustomer('checksum', 3);
    }

    // -------------------------------------------------------------------------
    // saveAddress
    // -------------------------------------------------------------------------

    public function testSaveAddressReturnsFalseWhenAddressIdIsZero(): void
    {
        $this->db->expects($this->never())->method('insert');

        $result = $this->repository->saveAddress(0, 5, 'mychecksum');

        $this->assertFalse($result);
    }

    public function testSaveAddressReturnsFalseWhenCustomerIdIsZero(): void
    {
        $this->db->expects($this->never())->method('insert');

        $result = $this->repository->saveAddress(10, 0, 'mychecksum');

        $this->assertFalse($result);
    }

    public function testSaveAddressReturnsTrueOnSuccess(): void
    {
        $this->db->method('insert')->willReturn(true);

        $result = $this->repository->saveAddress(10, 5, 'mychecksum');

        $this->assertTrue($result);
    }

    public function testSaveAddressReturnsFalseOnFailure(): void
    {
        $this->db->method('insert')->willReturn(false);

        $result = $this->repository->saveAddress(10, 5, 'mychecksum');

        $this->assertFalse($result);
    }

    public function testSaveAddressInsertsIntoPsCheckoutAddressTable(): void
    {
        $this->db->expects($this->once())
            ->method('insert')
            ->with(
                'pscheckout_address',
                $this->callback(function ($data) {
                    return isset($data['id_address'], $data['id_customer'], $data['checksum']);
                }),
                $this->anything(),
                $this->anything(),
                \Db::REPLACE
            )
            ->willReturn(true);

        $this->repository->saveAddress(10, 5, 'mychecksum');
    }

    public function testSaveAddressPassesCorrectIds(): void
    {
        $this->db->expects($this->once())
            ->method('insert')
            ->with(
                $this->anything(),
                $this->callback(function ($data) {
                    return $data['id_address'] === 99
                        && $data['id_customer'] === 7;
                }),
                $this->anything(),
                $this->anything(),
                $this->anything()
            )
            ->willReturn(true);

        $this->repository->saveAddress(99, 7, 'anychecksum');
    }

    // -------------------------------------------------------------------------
    // REPLACE semantics — same (id_customer, checksum) with different id_address
    //
    // The table PRIMARY KEY is (id_customer, checksum). Calling saveAddress with
    // the same customer+checksum but a new id_address (e.g. after PS clones a
    // deleted address) must overwrite the stale row, not accumulate a duplicate.
    // Db::REPLACE handles this at the driver level; here we verify the flag is
    // always set regardless of the address ID supplied.
    // -------------------------------------------------------------------------

    public function testSaveAddressAlwaysUsesReplaceRegardlessOfAddressId(): void
    {
        $calls = [];
        $this->db->method('insert')
            ->willReturnCallback(function ($table, $data, $nullValues, $useCache, $type) use (&$calls) {
                $calls[] = $type;

                return true;
            });

        $this->repository->saveAddress(5, 1, 'checksum');
        $this->repository->saveAddress(6, 1, 'checksum'); // same customer+checksum, new address (PS clone scenario)

        $this->assertCount(2, $calls);
        $this->assertSame(\Db::REPLACE, $calls[0]);
        $this->assertSame(\Db::REPLACE, $calls[1]);
    }
}
