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

export class ModalComponent extends BaseComponent {
  static Inject = {
    querySelectorService: 'QuerySelectorService',
    config: 'PsCheckoutConfig',
    $: '$'
  };

  created() {
    this.data.parent = this.querySelectorService.getLoaderParent();
    this.data.header = this.props.header || null;
    this.data.content = this.props.content || null;
    this.data.confirmText = this.props.confirmText || this.$('ok');
    this.data.confirmType = this.props.confirmType || 'primary';
    this.data.cancelText = this.props.cancelText || this.$('cancel');
    this.data.cancelType = this.props.cancelType || 'primary';
    this.data.onConfirm = this.props.onConfirm || (() => {});
    this.data.onClose = this.props.onClose || (() => {});
  }

  render() {
    this.overlay = document.createElement('div');
    this.overlay.classList.add('ps-checkout', 'overlay');

    this.overlay.addEventListener('click', (event) => {
      if (event.target === this.overlay) {
        this.data.onClose();
        this.hide();
      }
    })

    this.modal = document.createElement('div');
    this.modal.classList.add('ps-checkout', 'ps-checkout-modal');

    if (this.data.header) {
      this.header = document.createElement('h1');
      this.header.classList.add('ps-checkout', 'text');
      this.header.innerHTML = this.data.header;

      this.modal.append(this.header);
    }

    if (this.data.content) {
      this.contentContainer = document.createElement('div');
      this.contentContainer.classList.add('ps-checkout', 'content');
      this.contentContainer.append(this.data.content);
      this.modal.append(this.contentContainer);
    }

    this.footer = document.createElement('div');
    this.footer.classList.add('ps-checkout', 'footer');

    this.cancelButton = document.createElement('button');
    this.cancelButton.innerHTML = this.data.cancelText;
    this.cancelButton.classList.add('ps-checkout', 'btn', this.data.cancelType);

    this.cancelButton.addEventListener('click', (event) => {
      this.data.onClose();
      this.hide();
    })

    this.confirmButton = document.createElement('button');
    this.confirmButton.innerHTML = this.data.confirmText;
    this.confirmButton.classList.add('ps-checkout', 'btn', this.data.confirmType);

    this.confirmButton.addEventListener('click', (event) => {
      this.data.onConfirm();
      this.hide();
    })

    this.footer.append(this.cancelButton, this.confirmButton);

    this.modal.append(this.footer);

    this.overlay.append(this.modal);
    this.data.parent.append(this.overlay);

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
