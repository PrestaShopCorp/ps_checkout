<?php
/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License 3.0 (AFL-3.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 */

namespace PrestaShop\Module\PrestashopCheckout\OrderState;

use PrestaShop\Module\PrestashopCheckout\OrderState\Exception\OrderStateException;

class OrderState
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $configurationKey;

    /**
     * @var array
     */
    private $name;

    /**
     * @var string
     */
    private $color;

    /**
     * @var bool
     */
    private $logable;

    /**
     * @var bool
     */
    private $paid;

    /**
     * @var bool
     */
    private $invoice;

    /**
     * @var bool
     */
    private $pdfInvoice;

    /**
     * @var bool
     */
    private $shipped;

    /**
     * @var bool
     */
    private $delivery;

    /**
     * @var bool
     */
    private $pdfDelivery;

    /**
     * @var bool
     */
    private $sendMail;

    /**
     * @var bool
     */
    private $hidden;

    /**
     * @var bool
     */
    private $unremovable;

    /**
     * @var string
     */
    private $template;

    /**
     * @var bool
     */
    private $deleted;

    /**
     * @param string $configurationKey
     * @param array $name
     * @param string $color
     */
    public function __construct($configurationKey, array $name, $color)
    {
        $this->configurationKey = $configurationKey;
        $this->setName($name);
        $this->color = $color;
        $this->paid = false;
        $this->logable = false;
        $this->invoice = false;
        $this->pdfInvoice = false;
        $this->delivery = false;
        $this->pdfDelivery = false;
        $this->shipped = false;
        $this->sendMail = false;
        $this->hidden = false;
        $this->template = '';
        $this->deleted = false;
    }

    /**
     * @return array
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param array $nameByLangIsoCode
     *
     * @throws OrderStateException
     */
    public function setName(array $nameByLangIsoCode)
    {
        if (empty($nameByLangIsoCode)) {
            throw new OrderStateException(sprintf("The name field shouldn't be empty"), OrderStateException::ORDER_STATE_EMPTY_NAME);
        }

        $tabNameByLangId = [];
        foreach ($nameByLangIsoCode as $langIsoCode => $name) {
            foreach (\Language::getLanguages(false) as $language) {
                if (\Tools::strtolower($language['iso_code']) === $langIsoCode) {
                    $tabNameByLangId[(int) $language['id_lang']] = $name;
                } elseif (isset($nameByLangIsoCode['en'])) {
                    $tabNameByLangId[(int) $language['id_lang']] = $nameByLangIsoCode['en'];
                }
            }
        }
        $this->name = $tabNameByLangId;
    }

    /**
     * @return string
     */
    public function getColor()
    {
        return $this->color;
    }

    /**
     * @param string $color
     */
    public function setColor($color)
    {
        $this->color = $color;
    }

    /**
     * @return bool
     */
    public function isLogable()
    {
        return $this->logable;
    }

    /**
     * @param bool $logable
     */
    public function setLogable($logable)
    {
        $this->logable = $logable;
    }

    /**
     * @return bool
     */
    public function isPaid()
    {
        return $this->paid;
    }

    /**
     * @param bool $paid
     */
    public function setPaid($paid)
    {
        $this->paid = $paid;
    }

    /**
     * @return bool
     */
    public function isInvoice()
    {
        return $this->invoice;
    }

    /**
     * @param bool $invoice
     */
    public function setInvoice($invoice)
    {
        $this->invoice = $invoice;
    }

    /**
     * @return bool
     */
    public function isPdfInvoice()
    {
        return $this->pdfInvoice;
    }

    /**
     * @param bool $pdfInvoice
     */
    public function setPdfInvoice($pdfInvoice)
    {
        $this->pdfInvoice = $pdfInvoice;
    }

    /**
     * @return bool
     */
    public function isShipped()
    {
        return $this->shipped;
    }

    /**
     * @param bool $shipped
     */
    public function setShipped($shipped)
    {
        $this->shipped = $shipped;
    }

    /**
     * @return bool
     */
    public function isDelivery()
    {
        return $this->delivery;
    }

    /**
     * @param bool $delivery
     */
    public function setDelivery($delivery)
    {
        $this->delivery = $delivery;
    }

    /**
     * @return bool
     */
    public function isPdfDelivery()
    {
        return $this->pdfDelivery;
    }

    /**
     * @param bool $pdfDelivery
     */
    public function setPdfDelivery($pdfDelivery)
    {
        $this->pdfDelivery = $pdfDelivery;
    }

    /**
     * @return bool
     */
    public function isSendMail()
    {
        return $this->sendMail;
    }

    /**
     * @param bool $sendMail
     */
    public function setSendMail($sendMail)
    {
        $this->sendMail = $sendMail;
        if ($sendMail === false) {
            $this->template = '';
        }
    }

    /**
     * @return bool
     */
    public function isHidden()
    {
        return $this->hidden;
    }

    /**
     * @param bool $hidden
     */
    public function setHidden($hidden)
    {
        $this->hidden = $hidden;
    }

    /**
     * @return bool
     */
    public function isUnremovable()
    {
        return $this->unremovable;
    }

    /**
     * @param bool $unremovable
     */
    public function setUnremovable($unremovable)
    {
        $this->unremovable = $unremovable;
    }

    /**
     * @return string
     */
    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * @param string $template
     */
    public function setTemplate($template)
    {
        $this->sendMail = true;
        $this->template = $template;
    }

    /**
     * @return bool
     */
    public function isDeleted()
    {
        return $this->deleted;
    }

    /**
     * @param bool $deleted
     */
    public function setDeleted($deleted)
    {
        $this->deleted = $deleted;
    }

    /**
     * @return string
     */
    public function getConfigurationKey()
    {
        return $this->configurationKey;
    }

    /**
     * @param string $configurationKey
     */
    public function setConfigurationKey($configurationKey)
    {
        $this->configurationKey = $configurationKey;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @param string $moduleName
     *
     * @return bool
     *
     * @throws OrderStateException
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    public function save($moduleName)
    {
        $orderStatePS = new \PrestaShop\PrestaShop\Adapter\Entity\OrderState();
        $orderStatePS->module_name = $moduleName;
        $orderStatePS->name = $this->name;
        $orderStatePS->color = $this->color;
        $orderStatePS->logable = $this->logable;
        $orderStatePS->paid = $this->paid;
        $orderStatePS->invoice = $this->invoice;
        $orderStatePS->shipped = $this->shipped;
        $orderStatePS->delivery = $this->delivery;
        $orderStatePS->pdf_delivery = $this->pdfDelivery;
        $orderStatePS->pdf_invoice = $this->pdfInvoice;
        $orderStatePS->send_email = $this->sendMail;
        $orderStatePS->hidden = $this->hidden;
        $orderStatePS->unremovable = $this->unremovable;
        $orderStatePS->template = $this->template;
        $orderStatePS->deleted = $this->deleted;
        $result = (bool) $orderStatePS->add();

        if (false === $result) {
            throw new OrderStateException(sprintf(
                'Failed to create OrderState %s',
                $this->configurationKey
            ), OrderStateException::ORDER_STATE_NOT_CREATED);
        }

        $this->id = $orderStatePS->id;

        return true;
    }
}
