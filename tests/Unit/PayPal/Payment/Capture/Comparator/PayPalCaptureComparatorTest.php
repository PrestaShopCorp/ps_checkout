<?php

namespace Tests\Unit\PayPal\Payment\Capture\Comparator;

use PHPUnit\Framework\TestCase;
use PrestaShop\Module\PrestashopCheckout\PayPal\Payment\Capture\Comparator\PayPalCaptureComparator;
use Symfony\Component\Cache\Simple\ArrayCache;

class PayPalCaptureComparatorTest extends TestCase
{
    public function testCheckCaptureCompletedIsDifferent()
    {
        $firstCaptureOrder = json_decode($this->getFirstCaptureCompleted(), true);
        $cache = new ArrayCache();
        $cache->set($firstCaptureOrder['id'], $firstCaptureOrder);
        $secondCaptureOrder = json_decode($this->getSecondCaptureCompleted(), true);

        $comparator = new PayPalCaptureComparator($cache);

        $this->assertEquals(false, $comparator->compare($secondCaptureOrder));
    }

    public function testCheckCaptureCompletedIsSimilar()
    {
        $firstCaptureOrder = json_decode($this->getFirstCaptureCompleted(), true);
        $cache = new ArrayCache();
        $cache->set($firstCaptureOrder['id'], $firstCaptureOrder);

        $comparator = new PayPalCaptureComparator($cache);

        $this->assertEquals(true, $comparator->compare($firstCaptureOrder));
    }

    public function testCheckCapturePendingIsDifferent()
    {
        $firstCaptureOrder = json_decode($this->getFirstCapturePending(), true);
        $cache = new ArrayCache();
        $cache->set($firstCaptureOrder['id'], $firstCaptureOrder);
        $secondCaptureOrder = json_decode($this->getSecondCapturePending(), true);

        $comparator = new PayPalCaptureComparator($cache);

        $this->assertEquals(false, $comparator->compare($secondCaptureOrder));
    }

    public function testCheckCapturePendingIsSimilar()
    {
        $firstCaptureOrder = json_decode($this->getFirstCapturePending(), true);
        $cache = new ArrayCache();
        $cache->set($firstCaptureOrder['id'], $firstCaptureOrder);

        $comparator = new PayPalCaptureComparator($cache);

        $this->assertEquals(true, $comparator->compare($firstCaptureOrder));
    }

    public function testCheckCaptureDeniedIsDifferent()
    {
        $firstCaptureOrder = json_decode($this->getFirstCaptureDenied(), true);
        $cache = new ArrayCache();
        $cache->set($firstCaptureOrder['id'], $firstCaptureOrder);
        $secondCaptureOrder = json_decode($this->getSecondCaptureDenied(), true);

        $comparator = new PayPalCaptureComparator($cache);

        $this->assertEquals(false, $comparator->compare($secondCaptureOrder));
    }

    public function testCheckCaptureDeniedIsSimilar()
    {
        $firstCaptureOrder = json_decode($this->getFirstCaptureDenied(), true);
        $cache = new ArrayCache();
        $cache->set($firstCaptureOrder['id'], $firstCaptureOrder);

        $comparator = new PayPalCaptureComparator($cache);

        $this->assertEquals(true, $comparator->compare($firstCaptureOrder));
    }

    public function getFirstCaptureCompleted()
    {
        return '{
            "id": "0C0885549K6295627",
            "amount": {
                "currency_code": "USD",
                "value": "100.00"
            },
            "final_capture": true,
            "seller_protection": {
                "status": "ELIGIBLE",
                "dispute_categories": [
                    "ITEM_NOT_RECEIVED",
                    "UNAUTHORIZED_TRANSACTION"
                ]
            },
            "disbursement_mode": "INSTANT",
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
            "status": "COMPLETED",
            "supplementary_data": {
                "related_ids": {
                    "order_id": "03V05332DU5412024"
                }
            },
            "create_time": "2023-05-23T09:29:15Z",
            "update_time": "2023-05-23T09:29:15Z",
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
            ]
        }';
    }

    public function getSecondCaptureCompleted()
    {
        return '{
            "id": "86LF9BA1ATA95FF23",
            "amount": {
                "currency_code": "USD",
                "value": "88.00"
            },
            "final_capture": true,
            "seller_protection": {
                "status": "ELIGIBLE",
                "dispute_categories": [
                    "ITEM_NOT_RECEIVED",
                    "UNAUTHORIZED_TRANSACTION"
                ]
            },
            "disbursement_mode": "INSTANT",
            "seller_receivable_breakdown": {
                "gross_amount": {
                    "currency_code": "USD",
                    "value": "88.00"
                },
                "paypal_fee": {
                    "currency_code": "USD",
                    "value": "5.48"
                },
                "net_amount": {
                    "currency_code": "USD",
                    "value": "83.52"
                }
            },
            "status": "COMPLETED",
            "supplementary_data": {
                "related_ids": {
                    "order_id": "03V05332DU5412024"
                }
            },
            "create_time": "2023-05-23T09:29:15Z",
            "update_time": "2023-05-23T09:29:15Z",
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
            ]
        }';
    }

    public function getFirstCapturePending()
    {
        return '{
            "id": "8R008448MC910842W",
            "disbursement_mode": "INSTANT",
            "amount": {
              "value": "60.00",
              "currency_code": "USD"
            },
            "seller_protection": {
              "dispute_categories": [
                "ITEM_NOT_RECEIVED",
                "UNAUTHORIZED_TRANSACTION"
              ],
              "status": "ELIGIBLE"
            },
            "supplementary_data": {
              "related_ids": {
                "order_id": "8YS138211R2394904"
              }
            },
            "update_time": "2023-04-28T14:30:40Z",
            "create_time": "2023-04-28T14:30:40Z",
            "final_capture": false,
            "invoice_id": "1682692239",
            "links": [
              {
                "method": "GET",
                "rel": "self",
                "href": "https://api.sandbox.paypal.com/v2/payments/captures/8R008448MC910842W"
              },
              {
                "method": "POST",
                "rel": "refund",
                "href": "https://api.sandbox.paypal.com/v2/payments/captures/8R008448MC910842W/refund"
              },
              {
                "method": "GET",
                "rel": "up",
                "href": "https://api.sandbox.paypal.com/v2/payments/authorizations/1X092516FU319661J"
              }
            ],
            "status_details": {
              "reason": "RECEIVING_PREFERENCE_MANDATES_MANUAL_ACTION"
            },
            "status": "PENDING"
          }';
    }

    public function getSecondCapturePending()
    {
        return '{
            "id": "9IL74V1TT0R172947",
            "disbursement_mode": "INSTANT",
            "amount": {
              "value": "60.00",
              "currency_code": "USD"
            },
            "seller_protection": {
              "dispute_categories": [
                "ITEM_NOT_RECEIVED",
                "UNAUTHORIZED_TRANSACTION"
              ],
              "status": "ELIGIBLE"
            },
            "supplementary_data": {
              "related_ids": {
                "order_id": "8YS138211R2394904"
              }
            },
            "update_time": "2023-04-28T14:30:40Z",
            "create_time": "2023-04-28T14:30:40Z",
            "final_capture": false,
            "invoice_id": "1682692239",
            "links": [
              {
                "method": "GET",
                "rel": "self",
                "href": "https://api.sandbox.paypal.com/v2/payments/captures/8R008448MC910842W"
              },
              {
                "method": "POST",
                "rel": "refund",
                "href": "https://api.sandbox.paypal.com/v2/payments/captures/8R008448MC910842W/refund"
              },
              {
                "method": "GET",
                "rel": "up",
                "href": "https://api.sandbox.paypal.com/v2/payments/authorizations/1X092516FU319661J"
              }
            ],
            "status_details": {
              "reason": "RECEIVING_PREFERENCE_MANDATES_MANUAL_ACTION"
            },
            "status": "PENDING"
          }';
    }

    public function getFirstCaptureDenied()
    {
        return '{
            "id": "7NW873794T343360M",
            "amount": {
              "currency_code": "AUD",
              "value": "2.51"
            },
            "final_capture": true,
            "seller_protection": {
              "status": "ELIGIBLE",
              "dispute_categories": [
                "ITEM_NOT_RECEIVED",
                "UNAUTHORIZED_TRANSACTION"
              ]
            },
            "seller_receivable_breakdown": {
              "gross_amount": {
                "currency_code": "AUD",
                "value": "2.51"
              },
              "net_amount": {
                "currency_code": "AUD",
                "value": "2.51"
              }
            },
            "status": "DECLINED",
            "create_time": "2019-02-14T22:18:14Z",
            "update_time": "2019-02-14T22:20:01Z",
            "links": [
              {
                "href": "https://api.paypal.com/v2/payments/captures/7NW873794T343360M",
                "rel": "self",
                "method": "GET"
              },
              {
                "href": "https://api.paypal.com/v2/payments/captures/7NW873794T343360M/refund",
                "rel": "refund",
                "method": "POST"
              },
              {
                "href": "https://api.paypal.com/v2/payments/authorizations/2W543679LP5841156",
                "rel": "up",
                "method": "GET"
              }
            ]
          }';
    }

    public function getSecondCaptureDenied()
    {
        return '{
            "id": "8J7479BF00293401L",
            "amount": {
              "currency_code": "AUD",
              "value": "28.99"
            },
            "final_capture": true,
            "seller_protection": {
              "status": "ELIGIBLE",
              "dispute_categories": [
                "ITEM_NOT_RECEIVED",
                "UNAUTHORIZED_TRANSACTION"
              ]
            },
            "seller_receivable_breakdown": {
              "gross_amount": {
                "currency_code": "AUD",
                "value": "28.99"
              },
              "net_amount": {
                "currency_code": "AUD",
                "value": "28.99"
              }
            },
            "status": "DECLINED",
            "create_time": "2019-02-14T22:18:14Z",
            "update_time": "2019-02-14T22:20:01Z",
            "links": [
              {
                "href": "https://api.paypal.com/v2/payments/captures/7NW873794T343360M",
                "rel": "self",
                "method": "GET"
              },
              {
                "href": "https://api.paypal.com/v2/payments/captures/7NW873794T343360M/refund",
                "rel": "refund",
                "method": "POST"
              },
              {
                "href": "https://api.paypal.com/v2/payments/authorizations/2W543679LP5841156",
                "rel": "up",
                "method": "GET"
              }
            ]
          }';
    }
}
