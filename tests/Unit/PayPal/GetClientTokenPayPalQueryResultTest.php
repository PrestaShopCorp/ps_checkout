<?php

namespace Tests\Unit\Order;

use PHPUnit\Framework\TestCase;
use PrestaShop\Module\PrestashopCheckout\PayPal\Identity\Query\GetClientTokenPayPalQueryResult;

class GetClientTokenPayPalQueryResultTest extends TestCase
{
    public function testClientTokenIsNotEmpty()
    {
        $clientToken = new GetClientTokenPayPalQueryResult(1000, 'YY-mm-dd', 'YY-mm-dd');
        $this->assertNotEquals('', $clientToken->getClientToken());
    }

    public function testClientTokenExpireDateIsValid()
    {
        $clientToken = new GetClientTokenPayPalQueryResult(1000, '2022-08-31', '2022-08-30');
        $this->assertGreaterThan($clientToken->getCreatedAt(), $clientToken->getExpiresIn(), 'ExpiresAIn date is not greater than CreatedAt date');
    }

    public function testClientTokenErpireDateIsNotEmpty()
    {
        $clientToken = new GetClientTokenPayPalQueryResult(1000, '2022-08-31', '2022-08-30');
        $this->assertNotEquals('', $clientToken->getExpiresIn());
    }
}
