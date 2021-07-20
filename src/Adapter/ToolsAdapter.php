<?php

namespace PrestaShop\Module\PrestashopCheckout\Adapter;

use Tools;

class ToolsAdapter
{
    /**
     * @return bool
     */
    public function usingSecureMode()
    {
        return Tools::usingSecureMode();
    }
}
