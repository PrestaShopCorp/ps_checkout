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
export class NotificationComponent {
  constructor(checkout) {
    this.htmlElementService = checkout.htmlElementService;

    this.notificationPaymentContainer = this.htmlElementService.getNotificationPaymentContainer();
    this.notificationPaymentContainerTarget = this.htmlElementService.getNotificationPaymentContainerTarget();

    this.notificationPaymentCanceled = this.htmlElementService.getNotificationPaymentCanceled();

    this.notificationPaymentError = this.htmlElementService.getNotificationPaymentError();
    // this.notificationPaymentErrorText = this.htmlElementService.getNotificationPaymentErrorText();
  }

  render() {
    this.notificationPaymentContainerTarget.prepend(
      this.notificationPaymentContainer
    );

    return this;
  }

  hideCancelled() {
    this.notificationPaymentCanceled.style.display = 'none';
  }

  hideError() {
    this.notificationPaymentError.style.display = 'none';
  }

  showCanceled() {
    this.notificationPaymentCanceled.style.display = 'block';
  }

  showError(message) {
    this.notificationPaymentError.style.display = 'block';
    // this.notificationPaymentErrorText.textContent = message;
  }
}
