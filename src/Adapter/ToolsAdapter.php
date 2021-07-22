<?php

namespace PrestaShop\Module\PrestashopCheckout\Adapter;

use Context;
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

    public function displayPrice($price, $currency = null, $no_utf8 = false, Context $context = null)
    {
        return Tools::displayPrice($price, $currency, $no_utf8, $context);
    }
}
