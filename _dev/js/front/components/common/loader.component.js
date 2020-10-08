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
let STYLE;

export class LoaderComponent {
  constructor(checkout) {
    this.config = checkout.config;
    if (!STYLE) {
      STYLE = document.createElement('style');

      // TODO: Move to CSS
      STYLE.innerHTML = `
        .ps-checkout.overlay {
          visibility: hidden;
          opacity: 0;

          position: absolute;
          top: 0;
          left: 0;
          bottom: 0;
          right: 0;

          transition: opacity 0.5s linear;

          background-color: #00000040;
          z-index: 100000000;
        }

        .ps-checkout.overlay.visible {
          visibility: visible;
          opacity: 100;
        }

        .ps-checkout.popup {
          position: absolute;
          top: 0;
          left: 0;
          bottom: 0;
          right: 0;

          width: 400px;
          height: 250px;

          margin: auto;

          background-color: #fff;
          border-radius: 15px;
        }

        .ps-checkout.text, .ps-checkout.loader {
          display: block;
          margin: 0 auto;
          margin-top: 45px;
          text-align: center;
        }

        .ps-checkout.subtext {
          margin-top: 25px;
          text-align: center;
        }
    `;

      document.getElementsByTagName('head')[0].appendChild(STYLE);
    }
  }
  render() {
    this.overlay = document.createElement('div');
    this.overlay.classList.add('ps-checkout', 'overlay');

    this.popup = document.createElement('div');
    this.popup.classList.add('ps-checkout', 'popup');

    this.text = document.createElement('h1');
    this.text.classList.add('ps-checkout', 'text');
    this.text.innerHTML = 'Thanks for your purchase!';

    this.loader = document.createElement('img');
    this.loader.classList.add('ps-checkout', 'loader');
    this.loader.setAttribute('src', this.config.loaderImage);
    this.loader.setAttribute('alt', 'loader');

    this.subtext = document.createElement('div');
    this.subtext.classList.add('ps-checkout', 'subtext');
    this.subtext.innerHTML = 'Please wait, we proceed to payment';

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
