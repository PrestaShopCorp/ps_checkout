<?php
/**
* 2007-2019 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author    PrestaShop SA <contact@prestashop.com>
*  @copyright 2007-2019 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

namespace PrestaShop\Module\PrestashopCheckout\Builder\Payload;

/**
 * Base builder for others Payload Builder
 */
abstract class Builder implements PayloadBuilderInterface
{
    /**
     * @var Payload
     */
    private $payload;

    public function __construct()
    {
        $this->reset();
    }

    /**
     * Clean the payload
     */
    public function reset()
    {
        $this->payload = new Payload();
    }

    /**
     * Return the result of the payload and
     * clean the builder to be ready to producing a new payload
     *
     * @return Payload
     */
    public function presentPayload()
    {
        $payload = $this->payload;
        $this->reset();

        return $payload;
    }

    /**
     * Before build, reset the payload
     */
    public function buildFullPayload()
    {
        $this->reset();
    }

    /**
     * Before build, reset the payload
     */
    public function buildMinimalPayload()
    {
        $this->reset();
    }

    /**
     * Getter
     *
     * @return Payload
     */
    public function getPayload()
    {
        return $this->payload;
    }

    /**
     * Setter
     *
     * @param Payload $payload
     */
    public function setPayload($payload)
    {
        $this->payload = $payload;
    }
}
