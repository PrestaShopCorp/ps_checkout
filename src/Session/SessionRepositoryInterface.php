<?php

namespace PrestaShop\Module\PrestashopCheckout\Session;

interface SessionRepositoryInterface
{
    public function save(array $sessionData);

    public function get(array $sessionData);

    public function update(Session $session);

    public function remove($userId, $shopId, $isClosed);

    public function close($userId, $shopId, $isClosed);
}
