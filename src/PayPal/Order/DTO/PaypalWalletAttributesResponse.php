<?php

namespace PrestaShop\Module\PrestashopCheckout\PayPal\Order\DTO;

class PaypalWalletAttributesResponse
{
    /**
     * @var VaultResponse|null
     */
    protected $vault;
    /**
     * An array of merchant cobranded cards used by buyer to complete an order. This array will be present if a merchant has onboarded their cobranded card with PayPal and provided corresponding label(s).
     *
     * @var CobrandedCard[]|null
     */
    protected $cobranded_cards;
    /**
     * Constructor
     * @param mixed[] $data Associated array of property values initializing the model
     */
    public function __construct(array $data = null)
    {
        $this->vault = isset($data['vault']) ? $data['vault'] : null;
        $this->cobranded_cards = isset($data['cobranded_cards']) ? $data['cobranded_cards'] : null;
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
    /**
     * Gets cobranded_cards.
     *
     * @return CobrandedCard[]|null
     */
    public function getCobrandedCards()
    {
        return $this->cobranded_cards;
    }
    /**
     * Sets cobranded_cards.
     *
     * @param CobrandedCard[]|null $cobranded_cards  An array of merchant cobranded cards used by buyer to complete an order. This array will be present if a merchant has onboarded their cobranded card with PayPal and provided corresponding label(s).
     *
     * @return $this
     */
    public function setCobrandedCards(array $cobranded_cards = null)
    {
        $this->cobranded_cards = $cobranded_cards;
        return $this;
    }
}
