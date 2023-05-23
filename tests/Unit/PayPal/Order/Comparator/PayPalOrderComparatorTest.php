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

namespace Tests\Unit\PayPal\Order\Comparator;

use PHPUnit\Framework\TestCase;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\Comparator\PayPalOrderComparator;

class PayPalOrderComparatorTest extends TestCase
{
    public function testCheckPayPalCreatedIsDifferent()
    {
        $firstPayPalOrder = json_decode($this->getFirstOrderPayPalCreated(), true);
        $secondPayPalOrder = json_decode($this->getSecondOrderPayPalCreated(), true);

        $comparator = new PayPalOrderComparator($firstPayPalOrder, $secondPayPalOrder);

        $this->assertEquals(false, $comparator->isOrderPayPalSimilar());
    }

    public function testCheckPayPalCreatedIsSimilar()
    {
        $firstPayPalOrder = json_decode($this->getFirstOrderPayPalCreated(), true);

        $comparator = new PayPalOrderComparator($firstPayPalOrder, $firstPayPalOrder);

        $this->assertEquals(true, $comparator->isOrderPayPalSimilar());
    }

    public function testCheckPayPalPayerActionRequiredIsDifferent()
    {
        $firstPayPalOrder = json_decode($this->getFirstOrderPayPalPayerActionRequired(), true);
        $secondPayPalOrder = json_decode($this->getSecondOrderPayPalPayerActionRequired(), true);

        $comparator = new PayPalOrderComparator($firstPayPalOrder, $secondPayPalOrder);

        $this->assertEquals(false, $comparator->isOrderPayPalSimilar());
    }

    public function testCheckPayPalPayerActionRequiredIsSimilar()
    {
        $firstPayPalOrder = json_decode($this->getFirstOrderPayPalPayerActionRequired(), true);

        $comparator = new PayPalOrderComparator($firstPayPalOrder, $firstPayPalOrder);

        $this->assertEquals(true, $comparator->isOrderPayPalSimilar());
    }

    public function testCheckPayPalApprovedIsDifferent()
    {
        $firstPayPalOrder = json_decode($this->getFirstOrderPayPalApproved(), true);
        $secondPayPalOrder = json_decode($this->getSecondOrderPayPalApproved(), true);

        $comparator = new PayPalOrderComparator($firstPayPalOrder, $secondPayPalOrder);

        $this->assertEquals(false, $comparator->isOrderPayPalSimilar());
    }

    public function testCheckPayPalApprovedIsSimilar()
    {
        $firstPayPalOrder = json_decode($this->getFirstOrderPayPalApproved(), true);

        $comparator = new PayPalOrderComparator($firstPayPalOrder, $firstPayPalOrder);

        $this->assertEquals(true, $comparator->isOrderPayPalSimilar());
    }

    public function testCheckPayPalCompletedIsDifferent()
    {
        $firstPayPalOrder = json_decode($this->getFirstOrderPayPalCompleted(), true);
        $secondPayPalOrder = json_decode($this->getSecondOrderPayPalCompleted(), true);

        $comparator = new PayPalOrderComparator($firstPayPalOrder, $secondPayPalOrder);

        $this->assertEquals(false, $comparator->isOrderPayPalSimilar());
    }

    public function testCheckPayPalCompletedIsSimilar()
    {
        $firstPayPalOrder = json_decode($this->getFirstOrderPayPalCompleted(), true);

        $comparator = new PayPalOrderComparator($firstPayPalOrder, $firstPayPalOrder);

        $this->assertEquals(true, $comparator->isOrderPayPalSimilar());
    }

    public function getFirstOrderPayPalCreated()
    {
        return '{
            "id": "03V05332DU5412024",
            "intent": "CAPTURE",
            "status": "CREATED",
            "purchase_units": [
                {
                    "reference_id": "default",
                    "amount": {
                        "currency_code": "USD",
                        "value": "100.00",
                        "breakdown": {
                            "item_total": {
                                "currency_code": "USD",
                                "value": "100.00"
                            }
                        }
                    },
                    "payee": {
                        "email_address": "john_merchant@example.com",
                        "merchant_id": "C7CYMKZDG8D6E"
                    },
                    "items": [
                        {
                            "name": "T-Shirt",
                            "unit_amount": {
                                "currency_code": "USD",
                                "value": "100.00"
                            },
                            "quantity": "1",
                            "description": "Green XL"
                        }
                    ]
                }
            ],
            "create_time": "2023-05-23T09:27:26Z",
            "links": [
                {
                    "href": "https://api.sandbox.paypal.com/v2/checkout/orders/03V05332DU5412024",
                    "rel": "self",
                    "method": "GET"
                },
                {
                    "href": "https://www.sandbox.paypal.com/checkoutnow?token=03V05332DU5412024",
                    "rel": "approve",
                    "method": "GET"
                },
                {
                    "href": "https://api.sandbox.paypal.com/v2/checkout/orders/03V05332DU5412024",
                    "rel": "update",
                    "method": "PATCH"
                },
                {
                    "href": "https://api.sandbox.paypal.com/v2/checkout/orders/03V05332DU5412024/capture",
                    "rel": "capture",
                    "method": "POST"
                }
            ]
        }';
    }

    public function getSecondOrderPayPalCreated()
    {
        return '{
            "id": "747FL082645FO0392",
            "intent": "CAPTURE",
            "status": "CREATED",
            "purchase_units": [
                {
                    "reference_id": "default",
                    "amount": {
                        "currency_code": "USD",
                        "value": "100.00",
                        "breakdown": {
                            "item_total": {
                                "currency_code": "USD",
                                "value": "100.00"
                            }
                        }
                    },
                    "payee": {
                        "email_address": "john_merchant@example.com",
                        "merchant_id": "C7CYMKZDG8D6E"
                    },
                    "items": [
                        {
                            "name": "T-Shirt",
                            "unit_amount": {
                                "currency_code": "USD",
                                "value": "100.00"
                            },
                            "quantity": "1",
                            "description": "Green XL"
                        }
                    ]
                }
            ],
            "create_time": "2023-05-23T09:27:26Z",
            "links": [
                {
                    "href": "https://api.sandbox.paypal.com/v2/checkout/orders/03V05332DU5412024",
                    "rel": "self",
                    "method": "GET"
                },
                {
                    "href": "https://www.sandbox.paypal.com/checkoutnow?token=03V05332DU5412024",
                    "rel": "approve",
                    "method": "GET"
                },
                {
                    "href": "https://api.sandbox.paypal.com/v2/checkout/orders/03V05332DU5412024",
                    "rel": "update",
                    "method": "PATCH"
                },
                {
                    "href": "https://api.sandbox.paypal.com/v2/checkout/orders/03V05332DU5412024/capture",
                    "rel": "capture",
                    "method": "POST"
                }
            ]
        }';
    }

    public function getFirstOrderPayPalPayerActionRequired()
    {
        return '{
            "id": "1B4263080R024183U",
            "intent": "CAPTURE",
            "status": "PAYER_ACTION_REQUIRED",
            "payment_source": {
                "paypal": {
                    "email_address": "john@doe.com",
                    "name": {
                        "given_name": "John",
                        "surname": "Doe"
                    }
                }
            },
            "purchase_units": [
                {
                    "reference_id": "default",
                    "amount": {
                        "currency_code": "USD",
                        "value": "100.00",
                        "breakdown": {
                            "item_total": {
                                "currency_code": "USD",
                                "value": "100.00"
                            }
                        }
                    },
                    "payee": {
                        "email_address": "john_merchant@example.com",
                        "merchant_id": "C7CYMKZDG8D6E",
                        "display_data": {
                            "brand_name": "EXAMPLE INC"
                        }
                    },
                    "soft_descriptor": "JOHNMERCHAN",
                    "items": [
                        {
                            "name": "T-Shirt",
                            "unit_amount": {
                                "currency_code": "USD",
                                "value": "100.00"
                            },
                            "quantity": "1",
                            "description": "Green XL"
                        }
                    ]
                }
            ],
            "payer": {
                "name": {
                    "given_name": "John",
                    "surname": "Doe"
                },
                "email_address": "john@doe.com"
            },
            "links": [
                {
                    "href": "https://api.sandbox.paypal.com/v2/checkout/orders/1B4263080R024183U",
                    "rel": "self",
                    "method": "GET"
                },
                {
                    "href": "https://www.sandbox.paypal.com/checkoutnow?token=1B4263080R024183U",
                    "rel": "payer-action",
                    "method": "GET"
                }
            ]
        }';
    }

    public function getSecondOrderPayPalPayerActionRequired()
    {
        return '{
            "id": "8J7F990284F73L011",
            "intent": "CAPTURE",
            "status": "PAYER_ACTION_REQUIRED",
            "payment_source": {
                "paypal": {
                    "email_address": "john@doe.com",
                    "name": {
                        "given_name": "John",
                        "surname": "Doe"
                    }
                }
            },
            "purchase_units": [
                {
                    "reference_id": "default",
                    "amount": {
                        "currency_code": "USD",
                        "value": "100.00",
                        "breakdown": {
                            "item_total": {
                                "currency_code": "USD",
                                "value": "100.00"
                            }
                        }
                    },
                    "payee": {
                        "email_address": "john_merchant@example.com",
                        "merchant_id": "C7CYMKZDG8D6E",
                        "display_data": {
                            "brand_name": "EXAMPLE INC"
                        }
                    },
                    "soft_descriptor": "JOHNMERCHAN",
                    "items": [
                        {
                            "name": "T-Shirt",
                            "unit_amount": {
                                "currency_code": "USD",
                                "value": "100.00"
                            },
                            "quantity": "1",
                            "description": "Green XL"
                        }
                    ]
                }
            ],
            "payer": {
                "name": {
                    "given_name": "John",
                    "surname": "Doe"
                },
                "email_address": "john@doe.com"
            },
            "links": [
                {
                    "href": "https://api.sandbox.paypal.com/v2/checkout/orders/1B4263080R024183U",
                    "rel": "self",
                    "method": "GET"
                },
                {
                    "href": "https://www.sandbox.paypal.com/checkoutnow?token=1B4263080R024183U",
                    "rel": "payer-action",
                    "method": "GET"
                }
            ]
        }';
    }

    public function getFirstOrderPayPalApproved()
    {
        return '{
            "id": "03V05332DU5412024",
            "intent": "CAPTURE",
            "status": "APPROVED",
            "payment_source": {
                "paypal": {
                    "email_address": "sb-26qa712980505@personal.example.com",
                    "account_id": "VRT593XYPLRRJ",
                    "name": {
                        "given_name": "John",
                        "surname": "Doe"
                    },
                    "address": {
                        "country_code": "ES"
                    }
                }
            },
            "purchase_units": [
                {
                    "reference_id": "default",
                    "amount": {
                        "currency_code": "USD",
                        "value": "100.00",
                        "breakdown": {
                            "item_total": {
                                "currency_code": "USD",
                                "value": "100.00"
                            }
                        }
                    },
                    "payee": {
                        "email_address": "john_merchant@example.com",
                        "merchant_id": "C7CYMKZDG8D6E"
                    },
                    "soft_descriptor": "JOHNMERCHAN",
                    "items": [
                        {
                            "name": "T-Shirt",
                            "unit_amount": {
                                "currency_code": "USD",
                                "value": "100.00"
                            },
                            "quantity": "1",
                            "description": "Green XL"
                        }
                    ],
                    "shipping": {
                        "name": {
                            "full_name": "John Doe"
                        },
                        "address": {
                            "address_line_1": "calle Vilamar� 76993- 17469",
                            "admin_area_2": "Albacete",
                            "admin_area_1": "Albacete",
                            "postal_code": "02001",
                            "country_code": "ES"
                        }
                    }
                }
            ],
            "payer": {
                "name": {
                    "given_name": "John",
                    "surname": "Doe"
                },
                "email_address": "sb-26qa712980505@personal.example.com",
                "payer_id": "VRT593XYPLRRJ",
                "address": {
                    "country_code": "ES"
                }
            },
            "create_time": "2023-05-23T09:27:26Z",
            "links": [
                {
                    "href": "https://api.sandbox.paypal.com/v2/checkout/orders/03V05332DU5412024",
                    "rel": "self",
                    "method": "GET"
                },
                {
                    "href": "https://api.sandbox.paypal.com/v2/checkout/orders/03V05332DU5412024",
                    "rel": "update",
                    "method": "PATCH"
                },
                {
                    "href": "https://api.sandbox.paypal.com/v2/checkout/orders/03V05332DU5412024/capture",
                    "rel": "capture",
                    "method": "POST"
                }
            ]
        }';
    }

    public function getSecondOrderPayPalApproved()
    {
        return '{
            "id": "8U7635BB00L102904",
            "intent": "CAPTURE",
            "status": "APPROVED",
            "payment_source": {
                "paypal": {
                    "email_address": "sb-26qa712980505@personal.example.com",
                    "account_id": "VRT593XYPLRRJ",
                    "name": {
                        "given_name": "John",
                        "surname": "Doe"
                    },
                    "address": {
                        "country_code": "ES"
                    }
                }
            },
            "purchase_units": [
                {
                    "reference_id": "default",
                    "amount": {
                        "currency_code": "USD",
                        "value": "100.00",
                        "breakdown": {
                            "item_total": {
                                "currency_code": "USD",
                                "value": "100.00"
                            }
                        }
                    },
                    "payee": {
                        "email_address": "john_merchant@example.com",
                        "merchant_id": "C7CYMKZDG8D6E"
                    },
                    "soft_descriptor": "JOHNMERCHAN",
                    "items": [
                        {
                            "name": "T-Shirt",
                            "unit_amount": {
                                "currency_code": "USD",
                                "value": "100.00"
                            },
                            "quantity": "1",
                            "description": "Green XL"
                        }
                    ],
                    "shipping": {
                        "name": {
                            "full_name": "John Doe"
                        },
                        "address": {
                            "address_line_1": "calle Vilamar� 76993- 17469",
                            "admin_area_2": "Albacete",
                            "admin_area_1": "Albacete",
                            "postal_code": "02001",
                            "country_code": "ES"
                        }
                    }
                }
            ],
            "payer": {
                "name": {
                    "given_name": "John",
                    "surname": "Doe"
                },
                "email_address": "sb-26qa712980505@personal.example.com",
                "payer_id": "VRT593XYPLRRJ",
                "address": {
                    "country_code": "ES"
                }
            },
            "create_time": "2023-05-23T09:27:26Z",
            "links": [
                {
                    "href": "https://api.sandbox.paypal.com/v2/checkout/orders/03V05332DU5412024",
                    "rel": "self",
                    "method": "GET"
                },
                {
                    "href": "https://api.sandbox.paypal.com/v2/checkout/orders/03V05332DU5412024",
                    "rel": "update",
                    "method": "PATCH"
                },
                {
                    "href": "https://api.sandbox.paypal.com/v2/checkout/orders/03V05332DU5412024/capture",
                    "rel": "capture",
                    "method": "POST"
                }
            ]
        }';
    }

    public function getFirstOrderPayPalCompleted()
    {
        return '{
            "id": "2873R0D373T084752",
            "intent": "CAPTURE",
            "status": "COMPLETED",
            "payment_source": {
                "paypal": {
                    "email_address": "sb-26qa712980505@personal.example.com",
                    "account_id": "VRT593XYPLRRJ",
                    "name": {
                        "given_name": "John",
                        "surname": "Doe"
                    },
                    "address": {
                        "country_code": "ES"
                    }
                }
            },
            "purchase_units": [
                {
                    "reference_id": "default",
                    "amount": {
                        "currency_code": "USD",
                        "value": "100.00",
                        "breakdown": {
                            "item_total": {
                                "currency_code": "USD",
                                "value": "100.00"
                            },
                            "shipping": {
                                "currency_code": "USD",
                                "value": "0.00"
                            },
                            "handling": {
                                "currency_code": "USD",
                                "value": "0.00"
                            },
                            "insurance": {
                                "currency_code": "USD",
                                "value": "0.00"
                            },
                            "shipping_discount": {
                                "currency_code": "USD",
                                "value": "0.00"
                            }
                        }
                    },
                    "payee": {
                        "email_address": "john_merchant@example.com",
                        "merchant_id": "C7CYMKZDG8D6E"
                    },
                    "description": "T-Shirt",
                    "items": [
                        {
                            "name": "T-Shirt",
                            "unit_amount": {
                                "currency_code": "USD",
                                "value": "100.00"
                            },
                            "tax": {
                                "currency_code": "USD",
                                "value": "0.00"
                            },
                            "quantity": "1",
                            "description": "Green XL",
                            "image_url": ""
                        }
                    ],
                    "shipping": {
                        "name": {
                            "full_name": "John Doe"
                        },
                        "address": {
                            "address_line_1": "calle Vilamar� 76993- 17469",
                            "admin_area_2": "Albacete",
                            "admin_area_1": "Albacete",
                            "postal_code": "02001",
                            "country_code": "ES"
                        }
                    },
                    "payments": {
                        "captures": [
                            {
                                "id": "0C0885549K6295627",
                                "status": "COMPLETED",
                                "amount": {
                                    "currency_code": "USD",
                                    "value": "100.00"
                                },
                                "final_capture": true,
                                "disbursement_mode": "INSTANT",
                                "seller_protection": {
                                    "status": "ELIGIBLE",
                                    "dispute_categories": [
                                        "ITEM_NOT_RECEIVED",
                                        "UNAUTHORIZED_TRANSACTION"
                                    ]
                                },
                                "seller_receivable_breakdown": {
                                    "gross_amount": {
                                        "currency_code": "USD",
                                        "value": "100.00"
                                    },
                                    "paypal_fee": {
                                        "currency_code": "USD",
                                        "value": "5.48"
                                    },
                                    "net_amount": {
                                        "currency_code": "USD",
                                        "value": "94.52"
                                    }
                                },
                                "links": [
                                    {
                                        "href": "https://api.sandbox.paypal.com/v2/payments/captures/0C0885549K6295627",
                                        "rel": "self",
                                        "method": "GET"
                                    },
                                    {
                                        "href": "https://api.sandbox.paypal.com/v2/payments/captures/0C0885549K6295627/refund",
                                        "rel": "refund",
                                        "method": "POST"
                                    },
                                    {
                                        "href": "https://api.sandbox.paypal.com/v2/checkout/orders/03V05332DU5412024",
                                        "rel": "up",
                                        "method": "GET"
                                    }
                                ],
                                "create_time": "2023-05-23T09:29:15Z",
                                "update_time": "2023-05-23T09:29:15Z"
                            }
                        ]
                    }
                }
            ],
            "payer": {
                "name": {
                    "given_name": "John",
                    "surname": "Doe"
                },
                "email_address": "sb-26qa712980505@personal.example.com",
                "payer_id": "VRT593XYPLRRJ",
                "address": {
                    "country_code": "ES"
                }
            },
            "create_time": "2023-05-23T09:27:26Z",
            "update_time": "2023-05-23T09:36:15Z",
            "links": [
                {
                    "href": "https://api.sandbox.paypal.com/v2/checkout/orders/03V05332DU5412024",
                    "rel": "self",
                    "method": "GET"
                }
            ]
        }';
    }

    public function getSecondOrderPayPalCompleted()
    {
        return '{
            "id": "2873R0D373T084752",
            "intent": "CAPTURE",
            "status": "COMPLETED",
            "payment_source": {
                "paypal": {
                    "email_address": "sb-26qa712980505@personal.example.com",
                    "account_id": "VRT593XYPLRRJ",
                    "name": {
                        "given_name": "John",
                        "surname": "Doe"
                    },
                    "address": {
                        "country_code": "ES"
                    }
                }
            },
            "purchase_units": [
                {
                    "reference_id": "default",
                    "amount": {
                        "currency_code": "USD",
                        "value": "100.00",
                        "breakdown": {
                            "item_total": {
                                "currency_code": "USD",
                                "value": "100.00"
                            },
                            "shipping": {
                                "currency_code": "USD",
                                "value": "0.00"
                            },
                            "handling": {
                                "currency_code": "USD",
                                "value": "0.00"
                            },
                            "insurance": {
                                "currency_code": "USD",
                                "value": "0.00"
                            },
                            "shipping_discount": {
                                "currency_code": "USD",
                                "value": "0.00"
                            }
                        }
                    },
                    "payee": {
                        "email_address": "john_merchant@example.com",
                        "merchant_id": "C7CYMKZDG8D6E"
                    },
                    "description": "T-Shirt",
                    "items": [
                        {
                            "name": "T-Shirt",
                            "unit_amount": {
                                "currency_code": "USD",
                                "value": "100.00"
                            },
                            "tax": {
                                "currency_code": "USD",
                                "value": "0.00"
                            },
                            "quantity": "1",
                            "description": "Green XL",
                            "image_url": ""
                        }
                    ],
                    "shipping": {
                        "name": {
                            "full_name": "John Doe"
                        },
                        "address": {
                            "address_line_1": "calle Vilamar� 76993- 17469",
                            "admin_area_2": "Albacete",
                            "admin_area_1": "Albacete",
                            "postal_code": "02001",
                            "country_code": "ES"
                        }
                    },
                    "payments": {
                        "captures": [
                            {
                                "id": "0C0885549K6295627",
                                "status": "COMPLETED",
                                "amount": {
                                    "currency_code": "USD",
                                    "value": "100.00"
                                },
                                "final_capture": true,
                                "disbursement_mode": "INSTANT",
                                "seller_protection": {
                                    "status": "ELIGIBLE",
                                    "dispute_categories": [
                                        "ITEM_NOT_RECEIVED",
                                        "UNAUTHORIZED_TRANSACTION"
                                    ]
                                },
                                "seller_receivable_breakdown": {
                                    "gross_amount": {
                                        "currency_code": "USD",
                                        "value": "100.00"
                                    },
                                    "paypal_fee": {
                                        "currency_code": "USD",
                                        "value": "5.48"
                                    },
                                    "net_amount": {
                                        "currency_code": "USD",
                                        "value": "94.52"
                                    }
                                },
                                "links": [
                                    {
                                        "href": "https://api.sandbox.paypal.com/v2/payments/captures/0C0885549K6295627",
                                        "rel": "self",
                                        "method": "GET"
                                    },
                                    {
                                        "href": "https://api.sandbox.paypal.com/v2/payments/captures/0C0885549K6295627/refund",
                                        "rel": "refund",
                                        "method": "POST"
                                    },
                                    {
                                        "href": "https://api.sandbox.paypal.com/v2/checkout/orders/03V05332DU5412024",
                                        "rel": "up",
                                        "method": "GET"
                                    }
                                ],
                                "create_time": "2023-05-23T09:29:15Z",
                                "update_time": "2023-05-23T09:29:15Z"
                            }
                        ]
                    }
                }
            ],
            "payer": {
                "name": {
                    "given_name": "John",
                    "surname": "Doe"
                },
                "email_address": "sb-26qa712980505@personal.example.com",
                "payer_id": "VRT593XYPLRRJ",
                "address": {
                    "country_code": "ES"
                }
            },
            "create_time": "2023-05-23T09:27:26Z",
            "update_time": "2023-05-23T09:29:15Z",
            "links": [
                {
                    "href": "https://api.sandbox.paypal.com/v2/checkout/orders/03V05332DU5412024",
                    "rel": "self",
                    "method": "GET"
                }
            ]
        }';
    }
}
