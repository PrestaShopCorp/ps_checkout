<?php

namespace PrestaShop\Module\PrestashopCheckout\PayPal\Order\DTO;

class SupplementaryData
{
    /**
     * @var CardSupplementaryData|null
     */
    protected $card;

    /**
     * Constructor
     * @param mixed[] $data Associated array of property values initializing the model
     */
    public function __construct(array $data = null)
    {
        $this->card = isset($data['card']) ? $data['card'] : null;
    }

    /**
     * Gets card.
     *
     * @return CardSupplementaryData|null
     */
    public function getCard()
    {
        return $this->card;
    }

    /**
     * Sets card.
     *
     * @param CardSupplementaryData|null $card
     *
     * @return $this
     */
    public function setCard(CardSupplementaryData $card = null)
    {
        $this->card = $card;

        return $this;
    }
}


