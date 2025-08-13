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

namespace PsCheckout\Presentation\Presenter\Settings\Front;

use PsCheckout\Presentation\Presenter\PresenterInterface;

class FrontSettingsPresenter implements PresenterInterface
{
    /**
     * @var PresenterInterface[]
     */
    private $presenters;

    /**
     * @param PresenterInterface[] $presenters
     */
    public function __construct(array $presenters)
    {
        $this->presenters = $presenters;
    }

    /**
     * Build the JS media required by Front SDK
     *
     * @return array
     */
    public function present(): array
    {
        $settings = [];

        foreach ($this->presenters as $presenter) {
            if ($presenter instanceof PresenterInterface) {
                $settings = array_merge($settings, $presenter->present());
            }
        }

        return $settings;
    }
}
