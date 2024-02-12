<?php

namespace PrestaShop\Module\PrestashopCheckout\PayPal\Order\DTO;

class SellerProtection
{
    /**
     * Indicates whether the transaction is eligible for seller protection. For information, see [PayPal Seller Protection for Merchants](https://www.paypal.com/us/webapps/mpp/security/seller-protection).
     *
     * @var string|null
     */
    protected $status;

    /**
     * An array of conditions that are covered for the transaction.
     *
     * @var string[]|null
     */
    protected $dispute_categories;

    /**
     * Constructor
     *
     * @param mixed[] $data Associated array of property values initializing the model
     */
    public function __construct(array $data = null)
    {
        $this->status = isset($data['status']) ? $data['status'] : null;
        $this->dispute_categories = isset($data['dispute_categories']) ? $data['dispute_categories'] : null;
    }

    /**
     * Gets status.
     *
     * @return string|null
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Sets status.
     *
     * @param string|null $status Indicates whether the transaction is eligible for seller protection. For information, see [PayPal Seller Protection for Merchants](https://www.paypal.com/us/webapps/mpp/security/seller-protection).
     *
     * @return $this
     */
    public function setStatus($status = null)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Gets dispute_categories.
     *
     * @return string[]|null
     */
    public function getDisputeCategories()
    {
        return $this->dispute_categories;
    }

    /**
     * Sets dispute_categories.
     *
     * @param string[]|null $dispute_categories an array of conditions that are covered for the transaction
     *
     * @return $this
     */
    public function setDisputeCategories(array $dispute_categories = null)
    {
        $this->dispute_categories = $dispute_categories;

        return $this;
    }
}
