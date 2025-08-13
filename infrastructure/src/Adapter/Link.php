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

namespace PsCheckout\Infrastructure\Adapter;

class Link implements LinkInterface
{
    /**
     * @var ContextInterface
     */
    private $context;

    public function __construct(
        ContextInterface $context,
        string $moduleName
    ) {
        $this->context = $context;
        $this->moduleName = $moduleName;
    }

    /**
     * {@inheritdoc}
     */
    public function getAdminLink(string $controller, bool $withToken = true, array $sfRouteParams = [], array $params = []): string
    {
        $shop = $this->context->getShop();

        $adminLink = $this->context->getLink()->getAdminLink($controller, $withToken, $sfRouteParams, $params);

        if ($shop->virtual_uri !== '') {
            $adminLink = str_replace($shop->physical_uri . $shop->virtual_uri, $shop->physical_uri, $adminLink);
        }

        // We have problems with links in our zoid application, since some links generated don't have domain they redirect to CDN domain
        // Routes that use new symfony router are returned without the domain
        if (strpos($adminLink, 'http') !== 0) {
            return \Tools::getShopDomainSsl(true) . $adminLink;
        }

        return $adminLink;
    }

    /**
     * {@inheritdoc}
     */
    public function getModuleLink(string $controller, array $params = []): string
    {
        return $this->context->getLink()->getModuleLink(
            $this->moduleName,
            $controller,
            $params,
            true,
            $this->context->getLanguage()->id,
            $this->context->getShop()->id
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getPageLink(string $controller, array $params = []): string
    {
        return $this->context->getLink()->getPageLink($controller, true, $this->context->getLanguage()->id, $params);
    }
}
