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
import { BaseComponent } from '../../core/dependency-injection/base.component';

export class IframeComponent extends BaseComponent {
  static Inject = {
    querySelectorService: 'QuerySelectorService',
    config: 'PsCheckoutConfig',
    $: '$'
  };

  created() {
    this.data.parent = this.querySelectorService.getLoaderParent();
    this.data.src = this.props.src;
    this.data.onConfirm = this.props.onConfirm || (() => {});
    this.data.onClose = this.props.onClose || (() => {});
  }

  render() {
    this.overlay = document.createElement('div');
    this.overlay.classList.add('ps-checkout', 'overlay');

    this.iframe = document.createElement('iframe');
    this.iframe.src = this.data.src;
    this.iframe.classList.add('ps-checkout', 'ps-checkout-iframe');

    this.overlay.append(this.iframe);
    this.data.parent.append(this.overlay);

    return this;
  }

  reload(url = null) {
    if (url) {
      this.iframe.src = url;
    }
    this.iframe.contentWindow.location.reload();
  }

  show() {
    this.overlay.classList.add('visible');
    document.body.style.overflow = 'hidden';
  }

  hide() {
    this.overlay.classList.remove('visible');
    document.body.style.overflow = '';
  }
}
