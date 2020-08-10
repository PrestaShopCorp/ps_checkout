<?php
/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License 3.0 (AFL-3.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 */

namespace PrestaShop\Module\PrestashopCheckout\Segment;

use PrestaShop\Module\PrestashopCheckout\Environment\SegmentEnv;
use PrestaShop\Module\PrestashopCheckout\ShopUuidManager;

class_alias('Segment', 'Analytics');

/**
 * Class used to send informations to the Segment platform
 */
class SegmentTracker
{
    private $shopUuid;

    /**
     * SegmentTracker constructor.
     *
     * @param SegmentEnv $env
     * @param ShopUuidManager $shopUuid
     */
    public function __construct($env, $shopUuid)
    {
        \Segment::init($env->getSegmentApiKey());
        $this->shopUuid = $shopUuid;
    }

    public function identify($traits, $shops)
    {
        foreach($shops as $id) {
            \Segment::identify(array(
                "type" => "identify",
                "traits" => array(
                    "name" => "Peter Gibbons",
                    "email" => "peter@example.com",
                    "plan" => "premium",
                    "logins" => 5
                ),
                "userId" => $this->shopUuid->getForShop($id)
            ));
        }

        return \Segment::flush();
    }

    /**
     * @param string $message
     * @param array $shops
     */
    public function track($message, $shops)
    {
        foreach($shops as $id)
        {
            \Segment::track(array(
                'userId' => $this->shopUuid->getForShop($id),
                'event' => $message,
                'channel' => "browser",
                "context" => array(
                    "userAgent" => $_SERVER['HTTP_USER_AGENT']
                )
            ));
        }

        return \Segment::flush();
    }
}
