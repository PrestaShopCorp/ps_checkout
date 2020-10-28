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

export class LoaderComponent {
  constructor(checkout) {
    this.checkout = checkout;
    this.config = this.checkout.config;

    this.$ = this.checkout.$;
  }
  render() {
    this.overlay = document.createElement('div');
    this.overlay.classList.add('ps-checkout', 'overlay');

    this.popup = document.createElement('div');
    this.popup.classList.add('ps-checkout', 'popup');

    this.text = document.createElement('h1');
    this.text.classList.add('ps-checkout', 'text');
    this.text.innerHTML = this.$('loader-component.label.header');

    this.loader = document.createElement('img');
    this.loader.classList.add('ps-checkout', 'loader');
    this.loader.setAttribute('src', this.config.loaderImage);
    this.loader.setAttribute('alt', 'loader');

    this.subtext = document.createElement('div');
    this.subtext.classList.add('ps-checkout', 'subtext');
    this.text.innerHTML = this.$('loader-component.label.body');

    this.popup.append(this.text);
    this.popup.append(this.loader);
    this.popup.append(this.subtext);

    this.overlay.append(this.popup);
    document.body.append(this.overlay);

    return this;
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
