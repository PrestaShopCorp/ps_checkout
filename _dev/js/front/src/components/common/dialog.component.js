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
import { BaseComponent } from "../../core/dependency-injection/base.component";

export class DialogComponent extends BaseComponent {
  static Inject = {
    querySelectorService: 'QuerySelectorService'
  };

  created() {
    this.data.dialogTitle = this.props.dialogTitle;
    this.data.dialogMessage = this.props.dialogMessage;
    this.data.dialogButtonFalse = this.props.dialogButtonFalse;
    this.data.dialogButtonFalseCallback = this.props.dialogButtonFalseCallback
    this.data.dialogButtonTrue = this.props.dialogButtonTrue;
    this.data.dialogButtonTrueCallback = this.props.dialogButtonTrueCallback;

    this.data.parent = this.querySelectorService.getDialogParent();
  }

  render() {
    this.dialogOverlay = document.createElement('div');
    this.dialogOverlay.classList.add('ps-checkout', 'overlay');

    this.dialogPopup = document.createElement('div');
    this.dialogPopup.classList.add('ps-checkout', 'popup');

    this.dialogTitle = document.createElement('h1');
    this.dialogTitle.classList.add('ps-checkout', 'text');
    this.dialogTitle.innerHTML = this.data.dialogTitle;

    this.dialogMessage = document.createElement('div');
    this.dialogMessage.classList.add('ps-checkout', 'subtext');
    this.dialogMessage.innerHTML = this.data.dialogMessage;

    this.dialogButtonFalse = document.createElement('button');
    this.dialogButtonFalse.type = 'button';
    this.dialogButtonFalse.textContent = this.data.dialogButtonFalse;
    this.dialogButtonFalse.classList.add('ps-checkout', 'button');
    this.dialogButtonFalse.onclick = this.data.dialogButtonFalseCallback;

    this.dialogButtonTrue = document.createElement('button');
    this.dialogButtonTrue.type = 'button';
    this.dialogButtonTrue.textContent = this.data.dialogButtonTrue;
    this.dialogButtonTrue.classList.add('ps-checkout', 'button');
    this.dialogButtonTrue.onclick = this.data.dialogButtonTrueCallback;

    this.dialogButtonContainer = document.createElement('div');
    this.dialogButtonContainer.classList.add('ps-checkout', 'dialog-button-container');
    this.dialogButtonContainer.appendChild(this.dialogButtonFalse);
    this.dialogButtonContainer.appendChild(this.dialogButtonTrue);

    this.dialogPopup.append(this.dialogTitle);
    this.dialogPopup.append(this.dialogMessage);
    this.dialogPopup.append(this.dialogButtonContainer);

    this.dialogOverlay.append(this.dialogPopup);
    this.data.parent.append(this.dialogOverlay);

    return this;
  }

  show() {
    this.dialogOverlay.classList.add('visible');
    document.body.style.overflow = 'hidden';
  }

  hide() {
    this.dialogOverlay.classList.remove('visible');
    document.body.style.overflow = '';
  }
}
