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
import { NotificationComponent } from '../1_7/notification.component';
import { PaymentOptionsComponent } from '../1_7/payment-options.component';
import { ConditionsCheckboxComponent } from '../1_7/conditions-checkbox.component';
import { LoaderComponent } from '../common/loader.component';
import { PaymentOptionsLoaderComponent } from '../common/payment-options-loader.component';

export class PsCheckoutPs1_7Component extends BaseComponent {
  created() {
    this.app.root = this;
  }

  render() {
    this.children.paymentOptionsLoader = new PaymentOptionsLoaderComponent(
      this.app
    ).render();

    this.children.loader = new LoaderComponent(this.app).render();
    this.children.conditionsCheckbox = new ConditionsCheckboxComponent(
      this.app
    ).render();

    this.children.notification = new NotificationComponent(this.app).render();
    this.children.paymentOptions = new PaymentOptionsComponent(
      this.app
    ).render();

    this.children.paymentOptionsLoader.hide();

    return this;
  }
}
