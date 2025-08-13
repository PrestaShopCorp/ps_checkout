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

namespace PsCheckout\Infrastructure\Repository;

use Exception;
use PsCheckout\Core\Exception\PsCheckoutException;
use PsCheckout\Core\PayPal\Order\Entity\PayPalOrder;
use PsCheckout\Core\PayPal\Order\Repository\PayPalOrderRepositoryInterface;

class PayPalOrderRepository implements PayPalOrderRepositoryInterface
{
    const TABLE_NAME = 'pscheckout_order';

    /**
     * @var \Db
     */
    private $db;

    public function __construct(\Db $db)
    {
        $this->db = $db;
    }

    /**
     * {@inheritDoc}
     */
    public function getOneByCartId(int $cartId)
    {
        $query = new \DbQuery();

        $query->select('*');
        $query->from(self::TABLE_NAME);
        $query->where('id_cart=' . $cartId);

        $payPalOrderData = $this->db->getRow($query);

        if (!$payPalOrderData) {
            return null;
        }

        return $this->buildPayPalOrder($payPalOrderData);
    }

    /**
     * {@inheritDoc}
     */
    public function getOneBy(array $keyValueCriteria)
    {
        $query = new \DbQuery();
        $query->select('*');
        $query->from(self::TABLE_NAME);

        foreach ($keyValueCriteria as $key => $value) {
            $query->where(pSQL($key) . ' = "' . pSQL((string) $value) . '"');
        }

        $payPalOrderDataList = $this->db->executeS($query);

        if (empty($payPalOrderDataList)) {
            return null;
        }

        // Get the last row
        $payPalOrderData = end($payPalOrderDataList);

        return $this->buildPayPalOrder($payPalOrderData);
    }

    /**
     * {@inheritDoc}
     */
    public function save(PayPalOrder $payPalOrder): bool
    {
        $data = [
            'id_cart' => $payPalOrder->getIdCart(),
            'id' => pSQL($payPalOrder->getId()),
            'intent' => pSQL($payPalOrder->getIntent()),
            'funding_source' => pSQL($payPalOrder->getFundingSource()),
            'status' => pSQL($payPalOrder->getStatus()),
            'payment_source' => pSQL(json_encode($payPalOrder->getPaymentSource())),
            'environment' => pSQL($payPalOrder->getEnvironment()),
            'is_card_fields' => $payPalOrder->isCardFields(),
            'is_express_checkout' => $payPalOrder->isExpressCheckout(),
            'customer_intent' => pSQL(implode(',', $payPalOrder->getCustomerIntent())),
            'payment_token_id' => pSQL($payPalOrder->getPaymentTokenId()),
            'tags' => pSQL(implode(',', $payPalOrder->getTags())),
        ];

        // Check if record exists using primary key
        $exists = $this->getOneBy(['id' => $payPalOrder->getId()]);
        
        if ($exists) {
            return $this->db->update(
                self::TABLE_NAME,
                $data,
                'id = "' . pSQL($payPalOrder->getId()) . '"'
            );
        }

        return $this->db->insert(self::TABLE_NAME, $data);
    }

    /**
     * {@inheritDoc}
     */
    public function deletePayPalOrder(string $payPalOrderId): bool
    {
        try {
            $this->db->delete(self::TABLE_NAME, sprintf('`id` = "%s"', pSQL($payPalOrderId)));
            $this->db->delete('pscheckout_authorization', sprintf('`id_order` = "%s"', pSQL($payPalOrderId)));
            $this->db->delete('pscheckout_refund', sprintf('`id_order` = "%s"', pSQL($payPalOrderId)));
            $this->db->delete('pscheckout_capture', sprintf('`id_order` = "%s"', pSQL($payPalOrderId)));
            $this->db->delete('pscheckout_purchase_unit', sprintf('`id_order` = "%s"', pSQL($payPalOrderId)));
        } catch (Exception $exception) {
            throw new PsCheckoutException('Error while deleting PayPal Order', 0, $exception);
        }

        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function savePayPalOrder(PayPalOrder $payPalOrder): bool
    {
        $data = [
            'id' => pSQL($payPalOrder->getId()),
            'id_cart' => $payPalOrder->getIdCart(),
            'intent' => pSQL($payPalOrder->getIntent()),
            'funding_source' => pSQL($payPalOrder->getFundingSource()),
            'status' => pSQL($payPalOrder->getStatus()),
            'payment_source' => pSQL(json_encode($payPalOrder->getPaymentSource())),
            'environment' => pSQL($payPalOrder->getEnvironment()),
            'is_card_fields' => $payPalOrder->isCardFields(),
            'is_express_checkout' => $payPalOrder->isExpressCheckout(),
            'customer_intent' => pSQL(implode(',', $payPalOrder->getCustomerIntent())),
            'payment_token_id' => pSQL($payPalOrder->getPaymentTokenId()),
            'tags' => pSQL(implode(',', $payPalOrder->getTags())),
        ];

        return $this->db->insert(self::TABLE_NAME, $data);
    }

    /**
     * @param array $payPalOrderData
     *
     * @return PayPalOrder
     */
    private function buildPayPalOrder(array $payPalOrderData): PayPalOrder
    {
        $payPalOrder = new PayPalOrder(
            $payPalOrderData['id'],
            $payPalOrderData['id_cart'],
            $payPalOrderData['intent'],
            $payPalOrderData['funding_source'],
            $payPalOrderData['status'],
            json_decode($payPalOrderData['payment_source'], true),
            $payPalOrderData['environment'],
            (bool) $payPalOrderData['is_card_fields'],
            (bool) $payPalOrderData['is_express_checkout'],
            explode(',', $payPalOrderData['customer_intent']),
            $payPalOrderData['payment_token_id'] ?? '',
            explode(',', $payPalOrderData['tags'])
        );

        return $payPalOrder;
    }
}
