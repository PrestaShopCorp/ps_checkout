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

/**
 * @typedef MarkComponentProps
 *
 * @param {string} fundingSource.name
 * @param {*}      fundingSource.mark
 *
 * @param {HTMLElement} HTMLElement
 */

export class MarkComponent extends BaseComponent {
  static Inject = {
    config: 'PsCheckoutConfig'
  };

  created() {
    this.data.name = this.props.fundingSource.name;
    this.data.mark = this.props.fundingSource.mark;

    this.data.HTMLElement = this.props.HTMLElement;
    this.data.HTMLElementImage = this.props.HTMLElementImage || null;
  }

  hasCustomMark() {
    return this.config.customMark[this.data.name];
  }

  renderCustomMark() {
    const src = this.config.customMark[this.data.name];
    let logoList = [];

    if (this.config.cardSupportedBrands && this.config.cardLogoBrands) {
      this.config.cardSupportedBrands.forEach(brand => {
        if (this.config.cardLogoBrands[brand]) {
          let customMarkImg = document.createElement('img');
          customMarkImg.classList.add('cards-logo');
          customMarkImg.setAttribute('alt', brand);
          customMarkImg.setAttribute('src', this.config.cardLogoBrands[brand]);
          logoList.push(customMarkImg);
          let space = document.createElement('span');
          space.classList.add('paypal-button-space');
          space.innerText = ' ';
          logoList.push(space);
        }
      });
    } else {
      let customMarkImg = document.createElement('img');
      customMarkImg.classList.add('cards-logo');
      customMarkImg.setAttribute('alt', this.data.name);
      customMarkImg.setAttribute('src', src);
      logoList.push(customMarkImg);
    }

    this.data.HTMLElement.classList.add('paypal-mark');
    logoList.forEach(customMarkImg => this.data.HTMLElement.append(customMarkImg));
  }

  render() {
    this.data.HTMLElement.classList.add('ps_checkout-mark');
    this.data.HTMLElement.setAttribute('data-funding-source', this.data.name);

    if (this.hasCustomMark()) {
      this.renderCustomMark();
    } else {
      const markSelector = `.ps_checkout-mark[data-funding-source=${this.data.name}]`;
      this.data.mark.render(markSelector);
    }

    return this;
  }
}
