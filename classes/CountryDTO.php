<?php

class CountryDTO
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
     * @var string
     */
    private $name;

    public function __construct($id, $isoCode, $name)
    {
        $this->id = $id;
        $this->isoCode = $isoCode;
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
