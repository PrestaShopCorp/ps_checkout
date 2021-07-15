<?php

namespace Tests\Unit\Mock;

use PHPUnit\Framework\TestCase;

class MockedPsAccountsServiceTestCase extends TestCase
{
    public function getPsAccountsServiceMock()
    {
        return $this->getMockBuilder(\stdclass::class)
            ->setMethods([
                'getSuperAdminEmail',
                'getShopUuidV4',
                'getOrRefreshToken',
                'getRefreshToken',
                'getToken',
                'isEmailValidated',
                'getEmail',
                'isAccountLinked',
                'getAdminAjaxUrl',
            ])
            ->getMock();
    }
}
