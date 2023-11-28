<?php

namespace Tests\Unit\Rule;

use PHPUnit\Framework\TestCase;
use PrestaShop\Module\PrestashopCheckout\Rule\InRule;

class InRuleTest extends TestCase
{
    /**
     * @dataProvider evaluateProvider
     *
     * @return void
     */
    public function testEvaluate($rule, $result)
    {
        $inRule = new InRule($rule['value'], $rule['list']);
        $this->assertEquals($inRule->evaluate(), $result);
    }

    public function evaluateProvider()
    {
        return [
            [
                [
                    'value' => 3,
                    'list' => [1, 2, 3, 4],
                ],
                true,
            ],
            [
                [
                    'value' => 5,
                    'list' => [1, 2, 3, 4],
                ],
                false,
            ],
            [
                [
                    'value' => '4',
                    'list' => [1, 2, 3, 4],
                ],
                false,
            ],
        ];
    }
}
