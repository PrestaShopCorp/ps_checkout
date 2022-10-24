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

export class PayPalSdkComponent extends BaseComponent {
  static Inject = {
    config: 'PayPalSdkConfig'
  };

  created() {
    this.sdk = null;
    this.promise = null;
  }

  render() {
    const script = document.createElement('script');

    script.setAttribute('async', '');
    script.setAttribute('id', this.config.id);
    script.setAttribute('src', this.config.src);
    script.setAttribute('data-namespace', this.config.namespace);

    if (this.config.card3dsEnabled) {
      script.setAttribute('data-enable-3ds', '');
    }

    if (this.config.cspNonce) {
      script.setAttribute('data-csp-nonce', this.config.cspNonce);
    }

    if (this.config.orderId) {
      script.setAttribute('data-order-id', this.config.orderId);
    }

    if (this.config.partnerAttributionId) {
      script.setAttribute('data-partner-attribution-id', this.config.partnerAttributionId);
    }

    script.setAttribute('data-client-token', this.props.token);

    document.head.appendChild(script);

    this.promise = new Promise((resolve, reject) => {
      script.onload = () => {
        this.sdk = window[this.config.namespace];
        resolve(this.sdk);
      };

      script.onerror = () => {
        reject();
      };
    });

    return this;
  }
}
