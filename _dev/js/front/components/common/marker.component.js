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
export class MarkerComponent {
  constructor(checkout, fundingSource, htmlElementId) {
    this.checkout = checkout;
    this.config = this.checkout.config;

    this.fundingSource = fundingSource;
    this.htmlElementId = htmlElementId;
  }

  render() {
    if (this.config.customMarker[this.fundingSource.name]) {
      this.image = document.createElement('img');
      this.image.setAttribute('alt', this.fundingSource.name);
      this.image.setAttribute(
        'src',
        this.config.customMarker[this.fundingSource.name]
      );

      this.image.style.margin = '0 0.25em';

      document.querySelector(this.htmlElementId).append(this.image);
    } else {
      this.fundingSource.mark.render(this.htmlElementId);
    }

    return this;
  }
}
