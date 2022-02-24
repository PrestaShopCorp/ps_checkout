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
import { NotificationComponent } from '../1_6/notification.component';
import { PaymentOptionsComponent } from '../1_6/payment-options.component';
import { LoaderComponent } from '../common/loader.component';
import { PaymentOptionsLoaderComponent } from '../common/payment-options-loader.component';
import { ConditionsCheckboxComponent } from '../1_7/conditions-checkbox.component';

export class PsCheckoutPs1_6Component extends BaseComponent {
  static Inject = {
    prestashopService: 'PrestashopService',
    config: 'PsCheckoutConfig',
    $: '$'
  };

  created() {
    this.app.root = this;
  }

  renderCheckout() {
    this.children.paymentOptionsLoader = new PaymentOptionsLoaderComponent(
      this.app
    ).render();

    this.children.conditionsCheckbox = new ConditionsCheckboxComponent(
      this.app
    ).render();

    // TODO: Move this to HTMLElementService
    const cgv = document.getElementById('cgv');
    if ((cgv && cgv.checked) || !cgv) {
      this.children.notification = new NotificationComponent(
        this.app
      ).render();
      this.children.loader = new LoaderComponent(this.app).render();
      this.children.paymentOptions = new PaymentOptionsComponent(this.app, {
        markPosition: 'before'
      }).render();
    }

    this.children.paymentOptionsLoader.hide();
  }

  render() {
    this.renderCheckout();
    this.prestashopService.onUpdatePaymentMethods(() => {
      this.renderCheckout();
    });

    if (!this.config.paymentsReceivable) {
      this.children.notification.showNotice(this.$('error.paymentsNotReceivable'));
    }

    return this;
  }
}
