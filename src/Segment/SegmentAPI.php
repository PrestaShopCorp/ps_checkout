<?php
/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License version 3.0
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
 */

namespace PrestaShop\Module\PrestashopCheckout\Segment;

class SegmentAPI
{
    public function init($key, $options = [])
    {
        \Segment::init($key, $options);
    }

    public function track($message, $shops, $options = [])
    {
        if (\Tools::isPHPCLI()) {
            return true;
        }

        $userAgent = array_key_exists('HTTP_USER_AGENT', $_SERVER) === true ? $_SERVER['HTTP_USER_AGENT'] : '';
        $ip = array_key_exists('REMOTE_ADDR', $_SERVER) === true ? $_SERVER['REMOTE_ADDR'] : '';
        $path = strtok($_SERVER['REQUEST_URI'], '?');
        $referrer = array_key_exists('HTTP_REFERER', $_SERVER) === true ? $_SERVER['HTTP_REFERER'] : '';
        $queryString = '?' . $_SERVER['QUERY_STRING'];
        $url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

        foreach ($shops as $shopId) {
            \Segment::track([
                'userId' => $shopId,
                'event' => $message,
                'channel' => 'browser',
                'context' => [
                    'ip' => $ip,
                    'userAgent' => $userAgent,
                    'page' => [
                        'referrer' => $referrer,
                        'url' => $url,
                    ],
                ],
                'properties' => [
                    'psVersion' => _PS_VERSION_,
                    'moduleVersion' => \Ps_checkout::VERSION,
                    'path' => $path,
                    'search' => $queryString,
                    'category' => 'ps_checkout',
                ],
            ]);
        }

        return \Segment::flush();
    }
}
