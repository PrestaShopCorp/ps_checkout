<?php

namespace PrestaShop\Module\PrestashopCheckout\PayPal\Order\DTO;

class CardSupplementaryData
{
    /**
     * @var Level2CardProcessingData|null
     */
    protected $level_2;
    /**
     * @var Level3CardProcessingData|null
     */
    protected $level_3;
    /**
     * Constructor
     * @param mixed[] $data Associated array of property values initializing the model
     */
    public function __construct(array $data = null)
    {
        $this->level_2 = isset($data['level_2']) ? $data['level_2'] : null;
        $this->level_3 = isset($data['level_3']) ? $data['level_3'] : null;
    }
    /**
     * Gets level_2.
     *
     * @return Level2CardProcessingData|null
     */
    public function getLevel2()
    {
        return $this->level_2;
    }
    /**
     * Sets level_2.
     *
     * @param Level2CardProcessingData|null $level_2
     *
     * @return $this
     */
    public function setLevel2(Level2CardProcessingData $level_2 = null)
    {
        $this->level_2 = $level_2;
        return $this;
    }
    /**
     * Gets level_3.
     *
     * @return Level3CardProcessingData|null
     */
    public function getLevel3()
    {
        return $this->level_3;
    }
    /**
     * Sets level_3.
     *
     * @param Level3CardProcessingData|null $level_3
     *
     * @return $this
     */
    public function setLevel3(Level3CardProcessingData $level_3 = null)
    {
        $this->level_3 = $level_3;
        return $this;
    }
}
