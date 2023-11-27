<?php

namespace Tests\Unit\Cart;

use PHPUnit\Framework\TestCase;
use PrestaShop\Module\PrestashopCheckout\Cart\Cart;
use PrestaShop\Module\PrestashopCheckout\Cart\Exception\CartException;
use PrestaShop\Module\PrestashopCheckout\Discount\Discount;
use PrestaShop\Module\PrestashopCheckout\Product\Product;

class CartTest extends TestCase
{
    /**
     * @dataProvider invalidCartProvider
     * @throws CartException
     */
    public function test__constructInvalid($data,$exception)
    {
        $this->expectException($exception['class']);
        $this->expectExceptionCode($exception['code']);
        $this->expectExceptionMessage($exception['message']);
        new Cart($data['cartId'], $data['total'],$data['total_wt_taxes'],$data['products'],$data['discounts']);
    }

    /**
     * @throws CartException
     */
    public function invalidCartProvider()
    {
        return [
            [
                [
                    'cartId'=> 1,
                    'total'=> 10,
                    'total_wt_taxes' => '8.0000',
                    'products' => [
                        new Product('test','3.0000',2,true,true),
                        new Product('test2','6.0000',1,true,true),
                    ],
                    'discounts'=> [
                        new Discount('testD','2.0000')
                    ]
                ],
                [
                    'class' => CartException::class,
                    'code' => CartException::WRONG_TYPE_TOTAL,
                    'message' => 'TOTAL is not a string (integer)'
                ]
            ],
            [
                [
                    'cartId'=> 1,
                    'total'=> 'coucou',
                    'total_wt_taxes' => '8.0000',
                    'products' => [
                        new Product('test','3.0000',2,true,true),
                        new Product('test2','6.0000',1,true,true),
                    ],
                    'discounts'=> [
                        new Discount('testD','2.0000')
                    ]
                ],
                [
                    'class' => CartException::class,
                    'code' => CartException::WRONG_TYPE_TOTAL,
                    'message' => 'TOTAL is not numeric'
                ]
            ],
            [
                [
                    'cartId'=> 1,
                    'total'=> '10.0000',
                    'total_wt_taxes' => 8,4,
                    'products' => [
                        new Product('test','3.0000',2,true,true),
                        new Product('test2','6.0000',1,true,true),
                    ],
                    'discounts'=> [
                        new Discount('testD','2.0000')
                    ]
                ],
                [
                    'class' => CartException::class,
                    'code' => CartException::WRONG_TYPE_TOTAL_WT_TAXES,
                    'message' => 'TOTAL WT TAXES is not a string (integer)'
                ]
            ],
            [
                [
                    'cartId'=> 1,
                    'total'=> '10.0000',
                    'total_wt_taxes' => 'test34',
                    'products' => [
                        new Product('test','3.0000',2,true,true),
                        new Product('test2','6.0000',1,true,true),
                    ],
                    'discounts'=> [
                        new Discount('testD','2.0000')
                    ]
                ],
                [
                    'class' => CartException::class,
                    'code' => CartException::WRONG_TYPE_TOTAL_WT_TAXES,
                    'message' => 'TOTAL WT TAXES is not numeric'
                ]
            ],
        ];
    }
}

