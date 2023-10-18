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
use PrestaShop\Module\PrestashopCheckout\Adapter\LinkAdapter;

class AdminPaypalOnboardingPrestashopCheckoutController extends ModuleAdminController
{
    /**
     * @var Ps_checkout
     */
    public $module;

    /**
     * {@inheritdoc}
     */
    public function postProcess()
    {
        try {
            $idMerchant = Tools::getValue('merchantIdInPayPal');

            if (true === empty($idMerchant)) {
                $this->errors[] = $this->module->l('We didn\'t receive your PayPal Merchant identifier.');

                return false;
            }

            if (!Validate::isGenericName($idMerchant)) {
                $this->errors[] = $this->module->l('Your PayPal Merchant identifier seems invalid.');

                return false;
            }

            Tools::redirect(
                (new LinkAdapter($this->context->link))->getAdminLink(
                    'AdminModules',
                    true,
                    [],
                    [
                        'configure' => 'ps_checkout',
                    ]
                )
            );
        } catch (Exception $e) {
            $this->errors[] = $e->getMessage();
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function initCursedPage()
    {
        if (!$this->checkToken()) {
            $this->errors[] = $this->module->l('It seems your employee token is invalid.');
        }

        if (file_exists(_PS_ROOT_DIR_ . '/' . $this->admin_webpath . '/themes/' . $this->bo_theme . '/public/theme.css')) {
            $this->addCSS(__PS_BASE_URI__ . $this->admin_webpath . '/themes/' . $this->bo_theme . '/public/theme.css', 'all', 0);
        } elseif (file_exists(_PS_ROOT_DIR_ . '/' . $this->admin_webpath . '/themes/new-theme/public/theme.css')) {
            $this->addCSS(__PS_BASE_URI__ . $this->admin_webpath . '/themes/new-theme/public/theme.css', 'all', 1);
        } elseif (isset($this->bo_css) && file_exists(_PS_ROOT_DIR_ . '/' . $this->admin_webpath . '/themes/' . $this->bo_theme . '/css/' . $this->bo_css)) {
            $this->addCSS(__PS_BASE_URI__ . $this->admin_webpath . '/themes/' . $this->bo_theme . '/css/' . $this->bo_css, 'all', 0);
        }

        $this->context->smarty->assign([
            'img_dir' => _PS_IMG_,
            'iso' => $this->context->language->iso_code,
            'shop_name' => Configuration::get('PS_SHOP_NAME'),
            'meta_title' => $this->module->displayName,
            'navigationPipe' => Configuration::get('PS_NAVIGATION_PIPE') ? Configuration::get('PS_NAVIGATION_PIPE') : '>',
            'css_files' => $this->css_files,
            'js_files' => array_unique($this->js_files),
            'errors' => $this->errors,
            'logoSrc' => $this->module->getPathUri() . 'logo.png',
            'moduleLink' => (new LinkAdapter($this->context->link))->getAdminLink(
                'AdminModules',
                true,
                [],
                [
                    'configure' => 'ps_checkout',
                ]
            ),
        ]);

        echo $this->context->smarty->fetch($this->module->getLocalPath() . 'views/templates/admin/cursedPage.tpl');

        exit;
    }

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        if (!isset($this->context->employee) || !$this->context->employee->isLoggedBack()) {
            // Avoid redirection to Login page because we want display additional information
            $this->errors[] = $this->module->l('It seems you are logged out.');
            $this->initCursedPage();
        }

        parent::init();
    }

    /**
     * {@inheritdoc}
     */
    protected function isAnonymousAllowed()
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function display()
    {
        if ($this->errors) {
            $this->initCursedPage();
        }

        parent::display();
    }
}
