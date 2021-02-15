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
import { PsCheckoutConfig } from '../../config/ps-checkout.config';
import { PayPalServiceMock } from '../../../test/mocks/service/paypal.service';
import { PsCheckoutApiMock } from '../../../test/mocks/api/ps-checkout.api';
import { PaymentOptionsLoaderComponent } from './payment-options-loader.component';
import { SmartButtonComponent } from './smart-button.component';

function buildDIContainerMock() {
  return {
    root: {
      children: {
        conditionsCheckbox: jest.fn()
      }
    },
    container: {
      PsCheckoutConfig: {
        ...PsCheckoutConfig
      },
      PayPalService: PayPalServiceMock,
      PsCheckoutApi: PsCheckoutApiMock
    }
  };
}

describe('src/components/common/smart-button.component.spec.js', () => {
  afterEach(() => (document.body.innerHTML = ''));

  test('::render()', () => {
    document.body.innerHTML =
      '<div class="ps_checkout-button" data-funding-source="foo"></div>';

    const HTMLElement = document.querySelector('.ps_checkout-button');
    const fundingSource = {
      name: 'foo'
    };

    const diContainer = buildDIContainerMock();
    diContainer.container.PayPalService.getButtonPayment.mockImplementationOnce(
      () => {
        return diContainer.container.PayPalService;
      }
    );

    const smartButtonComponent = new SmartButtonComponent(diContainer, {
      HTMLElement,
      fundingSource
    });
    expect(smartButtonComponent.render()).toBe(smartButtonComponent);
    expect(diContainer.container.PayPalService.render).toHaveBeenCalledWith(
      `.ps_checkout-button[data-funding-source=${fundingSource.name}]`
    );
  });

  // TODO: Test listeners
});
