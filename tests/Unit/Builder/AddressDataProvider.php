<?php

namespace Tests\Unit\Builder;

final class AddressDataProvider
{
    public function getProvidedAddress()
    {
        return ['order' => [
        'payer' => [
            'name' => [
                'given_name' => 'Jonas',
                'surname' => 'Pinigas',
            ],
            'email_address' => 'jonas.pinigas@gmail.com',
            'address' => [
                'country_code' => 'LV',
            ],
        ],
        'shipping' => [
            'name' => [
                'full_name' => 'Mr.Jonas Pinigas',
            ],
            'address' => [
                'address_line_1' => 'Donelaicio 62',
                'admin_area_2' => 'Kaunas',
                'postal_code' => '20582',
                'country_code' => 'LT',
            ],
        ],
    ],
    ];
    }
}
