<?php

namespace PrestaShop\Module\PrestashopCheckout\PayPal\Order\DTO;
class CardAttributesResponse
{
        /**
     * @var VaultResponse|null
     */
    protected $vault;

    /**
     * Constructor
     * @param mixed[] $data Associated array of property values initializing the model
     */
    public function __construct(array $data = null)
    {
        $this->vault = isset($data['vault']) ? $data['vault'] : null;
    }

    /**
     * Gets vault.
     *
     * @return VaultResponse|null
     */
    public function getVault()
    {
        return $this->vault;
    }

    /**
     * Sets vault.
     *
     * @param VaultResponse|null $vault
     *
     * @return $this
     */
    public function setVault(VaultResponse $vault = null)
    {
        $this->vault = $vault;

        return $this;
    }
}


