<?php


use PrestaShop\Module\PrestashopCheckout\Session\SessionHelper;
use PrestaShop\Module\PrestashopCheckout\Session\Session;
use PHPUnit\Framework\TestCase;

class SessionHelperTest extends TestCase
{
    /**
     * @dataProvider getExpirationDates
     *
     * @param array sessionData
     */
    public function testIsExpired(array $sessionData, $result)
    {
        $session = new Session($sessionData);

        $this->assertEquals($result, SessionHelper::isExpired($session));
    }

    /**
     * @dataProvider getArraysToSort
     *
     * @param array $array
     * @param array $result
     */
    public function testSortMultidimensionalArray(array $array, array $result)
    {
        $this->assertSame($result, SessionHelper::sortMultidimensionalArray($array));
    }

    /**
     * @return array
     */
    public function getExpirationDates()
    {
        return [
            [
                'sessionData' => [
                    'expires_at' => '2021-01-01',
                    'correlation_id' => '',
                    'mode' => '',
                    'user_id' => '',
                    'shop_id' => '',
                    'is_closed' => '',
                    'auth_token' => '',
                    'status' => '',
                    'created_at' => '',
                    'updated_at' => '',
                    'closed_at' => '',
                    'is_sse_opened' => '',
                    'data' => '',
                ],
                'result' => true
            ],
            [
                'sessionData' => [
                    'expires_at' => '3021-01-01',
                    'correlation_id' => '',
                    'mode' => '',
                    'user_id' => '',
                    'shop_id' => '',
                    'is_closed' => '',
                    'auth_token' => '',
                    'status' => '',
                    'created_at' => '',
                    'updated_at' => '',
                    'closed_at' => '',
                    'is_sse_opened' => '',
                    'data' => '',
                ],
                'result' => false
            ]
        ];
    }

    /**
     * @return array
     */
    public function getArraysToSort()
    {
        return array(
            [
                'array' => [
                    'z' => [
                        'expires_at' => true,
                        'data' => [
                            'closed' => true,
                        ]
                    ],
                    'a' => true,
                ],
                'result' => [
                    'a' => true,
                    'z' => [
                        'data' => [
                            'closed' => true,
                        ],
                        'expires_at' => true,
                    ],
                ]
            ],
            [
                'array' => [
                    'a' => true,
                    'z' => [
                        'data' => [
                            'closed' => true,
                        ],
                        'expires_at' => true,
                    ],
                ],
                'result' => [
                    'a' => true,
                    'z' => [
                        'data' => [
                            'closed' => true,
                        ],
                        'expires_at' => true,
                    ],
                ]
            ],
        );
    }
}
