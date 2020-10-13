<?php
/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0  Academic Free License (AFL 3.0)
 */

namespace PrestaShop\Module\PrestashopCheckout\Environment;

/**
 * Class SentryEnv used to get the sentry environnment information from .env file
 */
class SentryEnv extends Env
{
    /**
     * @var string
     */
    private $key;

    /**
     * @var string
     */
    private $organisation;

    /**
     * @var string
     */
    private $project;

    /**
     * SentryEnv constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->setKey($this->getEnv('VUE_APP_SENTRY_KEY'));
        $this->setOrganisation($this->getEnv('VUE_APP_SENTRY_ORGANIZATION'));
        $this->setProject($this->getEnv('VUE_APP_SENTRY_PROJECT'));
    }

    /**
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @param string $key
     */
    public function setKey($key)
    {
        $this->key = $key;
    }

    /**
     * @return string
     */
    public function getOrganisation()
    {
        return $this->organisation;
    }

    /**
     * @param string $organisation
     */
    public function setOrganisation($organisation)
    {
        $this->organisation = $organisation;
    }

    /**
     * @return string
     */
    public function getProject()
    {
        return $this->project;
    }

    /**
     * @param string $project
     */
    public function setProject($project)
    {
        $this->project = $project;
    }
}
