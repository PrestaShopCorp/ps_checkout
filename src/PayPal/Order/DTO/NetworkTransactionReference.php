<?php

namespace PrestaShop\Module\PrestashopCheckout\PayPal\Order\DTO;

class NetworkTransactionReference
{
    /**
     * Transaction reference id returned by the scheme. For Visa and Amex, this is the \&quot;Tran id\&quot; field in response. For MasterCard, this is the \&quot;BankNet reference id\&quot; field in response. For Discover, this is the \&quot;NRID\&quot; field in response. The pattern we expect for this field from Visa/Amex/CB/Discover is numeric, Mastercard/BNPP is alphanumeric and Paysecure is alphanumeric with special character -.
     *
     * @var string
     */
    protected $id;

    /**
     * The date that the transaction was authorized by the scheme. This field may not be returned for all networks. MasterCard refers to this field as \&quot;BankNet reference date.
     *
     * @var string|null
     */
    protected $date;

    /**
     * @var string|null
     */
    protected $network;

    /**
     * Reference ID issued for the card transaction. This ID can be used to track the transaction across processors, card brands and issuing banks.
     *
     * @var string|null
     */
    protected $acquirer_reference_number;

    /**
     * Constructor
     *
     * @param mixed[] $data Associated array of property values initializing the model
     */
    public function __construct(array $data = null)
    {
        $this->id = isset($data['id']) ? $data['id'] : null;
        $this->date = isset($data['date']) ? $data['date'] : null;
        $this->network = isset($data['network']) ? $data['network'] : null;
        $this->acquirer_reference_number = isset($data['acquirer_reference_number']) ? $data['acquirer_reference_number'] : null;
    }

    /**
     * Gets id.
     *
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Sets id.
     *
     * @param string $id Transaction reference id returned by the scheme. For Visa and Amex, this is the \"Tran id\" field in response. For MasterCard, this is the \"BankNet reference id\" field in response. For Discover, this is the \"NRID\" field in response. The pattern we expect for this field from Visa/Amex/CB/Discover is numeric, Mastercard/BNPP is alphanumeric and Paysecure is alphanumeric with special character -.
     *
     * @return $this
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Gets date.
     *
     * @return string|null
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Sets date.
     *
     * @param string|null $date The date that the transaction was authorized by the scheme. This field may not be returned for all networks. MasterCard refers to this field as \"BankNet reference date.
     *
     * @return $this
     */
    public function setDate($date = null)
    {
        $this->date = $date;

        return $this;
    }

    /**
     * Gets network.
     *
     * @return string|null
     */
    public function getNetwork()
    {
        return $this->network;
    }

    /**
     * Sets network.
     *
     * @param string|null $network
     *
     * @return $this
     */
    public function setNetwork($network = null)
    {
        $this->network = $network;

        return $this;
    }

    /**
     * Gets acquirer_reference_number.
     *
     * @return string|null
     */
    public function getAcquirerReferenceNumber()
    {
        return $this->acquirer_reference_number;
    }

    /**
     * Sets acquirer_reference_number.
     *
     * @param string|null $acquirer_reference_number Reference ID issued for the card transaction. This ID can be used to track the transaction across processors, card brands and issuing banks.
     *
     * @return $this
     */
    public function setAcquirerReferenceNumber($acquirer_reference_number = null)
    {
        $this->acquirer_reference_number = $acquirer_reference_number;

        return $this;
    }
}
