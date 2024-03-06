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
import { DialogComponent } from "./dialog.component";

export class CancelDialogComponent extends DialogComponent {
  static Inject = {
    psCheckoutApi: 'PsCheckoutApi',
    $: '$'
  };

  created() {
    this.props.dialogTitle = this.$('dialog-cancel-payment.label.dialogTitle');
    this.props.dialogMessage = this.$('dialog-cancel-payment.label.dialogMessage');
    this.props.dialogButtonFalse = this.$('dialog-cancel-payment.label.dialogButtonFalse');
    this.props.dialogButtonTrue = this.$('dialog-cancel-payment.label.dialogButtonTrue');

    this.data.smartButtonData = this.props.smartButtonData;
    this.data.fundingSource = this.props.fundingSource;
    this.data.isExpressCheckout = this.props.isExpressCheckout;

    this.data.loader = this.app.root.children.loader;
    this.data.notification = this.app.root.children.notification;

    super.created();
  }

  cancelOrder() {
    let cancelData = this.psCheckoutApi
      .postCancelOrder({
        ...this.data.smartButtonData,
        fundingSource: this.data.fundingSource,
        isExpressCheckout: this.data.isExpressCheckout
      })
      .catch(error => {
        this.data.loader.hide();
        this.data.notification.showError(error.message);
      });
    this.hide();

    return cancelData;
  }

  show() {
    super.show();

    return new Promise((resolve) => {
      this.dialogButtonTrue.addEventListener('click', () => {
        resolve(this.cancelOrder());
      });

      this.dialogButtonFalse.addEventListener('click', () => {
        // TODO: What do we exactly do when user does not cancel
        // TODO: Redirect him to a specific page ? Leave him in payment tunnel ?
      });
    });
  }
}
