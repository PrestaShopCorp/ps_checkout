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

namespace Tests\Unit\PsCheckout\Infrastructure\Action;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use PsCheckout\Core\PayPal\Order\Entity\PayPalOrder;
use PsCheckout\Core\PayPal\Order\Repository\PayPalOrderRepositoryInterface;
use PsCheckout\Infrastructure\Action\SaveExpressCheckoutFlagsAction;

class SaveExpressCheckoutFlagsActionTest extends TestCase
{
    /** @var PayPalOrderRepositoryInterface|MockObject */
    private $repository;

    /** @var SaveExpressCheckoutFlagsAction */
    private $action;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(PayPalOrderRepositoryInterface::class);
        $this->action = new SaveExpressCheckoutFlagsAction($this->repository);
    }

    public function testExecuteDoesNothingWhenOrderNotFound(): void
    {
        $this->repository->expects($this->once())
            ->method('getOneBy')
            ->with(['id' => 'ORDER-MISSING'])
            ->willReturn(null);

        $this->repository->expects($this->never())
            ->method('save');

        $this->action->execute('ORDER-MISSING', 'paypal');
    }

    public function testExecuteSetsExpressCheckoutFlags(): void
    {
        $order = new PayPalOrder(
            'ORDER-123',
            42,
            'CAPTURE',
            'card',
            'APPROVED',
            [],
            'PRODUCTION',
            true,
            false,
            []
        );

        $this->repository->expects($this->once())
            ->method('getOneBy')
            ->with(['id' => 'ORDER-123'])
            ->willReturn($order);

        $this->repository->expects($this->once())
            ->method('save')
            ->with($this->callback(function (PayPalOrder $saved) {
                return $saved->getFundingSource() === 'paypal'
                    && $saved->isExpressCheckout() === true
                    && $saved->isCardFields() === false;
            }))
            ->willReturn(true);

        $this->action->execute('ORDER-123', 'paypal');
    }

    /**
     * @return array<string, array{string}>
     */
    public function provideFundingSources(): array
    {
        return [
            'paypal'       => ['paypal'],
            'venmo'        => ['venmo'],
            'paylater'     => ['paylater'],
            'card'         => ['card'],
        ];
    }

    /**
     * @dataProvider provideFundingSources
     */
    public function testExecuteSetsProvidedFundingSource(string $fundingSource): void
    {
        $order = new PayPalOrder(
            'ORDER-456',
            1,
            'CAPTURE',
            'paypal',
            'APPROVED',
            [],
            'PRODUCTION',
            false,
            false,
            []
        );

        $this->repository->method('getOneBy')->willReturn($order);

        $this->repository->expects($this->once())
            ->method('save')
            ->with($this->callback(function (PayPalOrder $saved) use ($fundingSource) {
                return $saved->getFundingSource() === $fundingSource;
            }));

        $this->action->execute('ORDER-456', $fundingSource);
    }

    public function testExecuteAlwaysClearsCardFieldsFlag(): void
    {
        $order = new PayPalOrder(
            'ORDER-789',
            1,
            'CAPTURE',
            'card',
            'APPROVED',
            [],
            'PRODUCTION',
            true,
            false,
            []
        );

        $this->repository->method('getOneBy')->willReturn($order);

        $this->repository->expects($this->once())
            ->method('save')
            ->with($this->callback(function (PayPalOrder $saved) {
                return $saved->isCardFields() === false
                    && $saved->isExpressCheckout() === true;
            }));

        $this->action->execute('ORDER-789', 'paypal');
    }
}
