<?php

class CurrencyDTO
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $isoCode;

    /**
     * @var int
     */
    private $precision;

    /**
     * @var int
     */
    private $decimal;

    /**
     * @var string
     */
    private $name;

    public function __construct($id, $isoCode, $precision, $decimal, $name)
    {
        $this->id = $id;
        $this->isoCode = $isoCode;
        $this->precision = $precision;
        $this->decimal = $decimal;
        $this->name = $name;
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
     * @return string
     */
    public function getIsoCode()
    {
        return $this->isoCode;
    }

    /**
     * @param string $isoCode
     */
    public function setIsoCode($isoCode)
    {
        $this->isoCode = $isoCode;
    }

    /**
     * @return int
     */
    public function getPrecision()
    {
        return $this->precision;
    }

    /**
     * @param int $precision
     */
    public function setPrecision($precision)
    {
        $this->precision = $precision;
    }

    /**
     * @return int
     */
    public function getDecimal()
    {
        return $this->decimal;
    }

    /**
     * @param int $decimal
     */
    public function setDecimal($decimal)
    {
        $this->decimal = $decimal;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }
}
