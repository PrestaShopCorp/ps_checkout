<?php

namespace PrestaShop\Module\PrestashopCheckout\Temp\Adapter;

class OrderDataAdapter
{
    /**
     * @param int $idGender
     * @param int|null $idLang
     * @param int|null $idShop
     *
     * @return string
     */
    public function getGenderName($idGender, $idLang = null, $idShop = null)
    {
        $gender = new \GenderCore($idGender, $idLang, $idShop);

        return $gender->name;
    }

    /**
     * @param int $idState
     *
     * @return bool|string
     */
    public function getStateName($idState)
    {
        return \StateCore::getNameById($idState);
    }

    /**
     * @param int $idCountry
     *
     * @return bool|string
     */
    public function getIsoCountry($idCountry) {
        return \CountryCore::getIsoById($idCountry);
    }
}
