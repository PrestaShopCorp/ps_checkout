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
import { BaseComponent } from '../../core/base.component';

/**
 * @typedef MarkComponentProps
 *
 * @param {string} fundingSource.name
 * @param {*}      fundingSource.mark
 *
 * @param {HTMLElement} HTMLElement
 * @param {HTMLElement} [HTMLElementImage]
 */

export class MarkComponent extends BaseComponent {
  static INJECT = {
    config: 'config'
  };

  /**
   *
   * @param app
   * @param {MarkComponentProps} props
   */
  constructor(app, props) {
    super(app, props);

    this.data.name = props.fundingSource.name;
    this.data.mark = props.fundingSource.mark;

    this.data.HTMLElement = props.HTMLElement;
    this.data.HTMLElementImage = props.HTMLElementImage || null;
  }

  hasCustomMark() {
    return this.config.customMark[this.data.name];
  }

  renderCustomMark() {
    const src = this.config.customMark[this.data.name];

    this.data.HTMLElementImage = document.createElement('img');
    this.data.HTMLElementImage.classList.add('ps-checkout-funding-img');
    this.data.HTMLElementImage.setAttribute('alt', this.data.name);
    this.data.HTMLElementImage.setAttribute('src', src);

    this.data.HTMLElement.append(this.data.HTMLElementImage);
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
