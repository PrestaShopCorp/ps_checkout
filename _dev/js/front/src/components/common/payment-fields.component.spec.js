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
import {PaymentFieldsComponent} from "./payment-fields.component";

function buildDIContainerMock() {
  return {
    container: {
      PsCheckoutConfig: {
        customMark: {
          bar: 'baz'
        }
      },
      PayPalService: {
        getPaymentFields: jest.fn()
      }
    }
  };
}

describe('src/components/common/payment-fields.component.spec.js', () => {
  beforeEach(() => (document.body.innerHTML = `<div id="foo" />`));
  afterEach(() => (document.body.innerHTML = ''));

  test('::render()', () => {
    document.body.innerHTML =
      '<div id="foo-payment-option-container">' +
      ' <div id="foo-label">Foo</div>' +
      ' <div id="foo-payment-option"></div>' +
      ' <div id="pay-with-payment-option-foo-form"></div>' +
      ' <div class="ps_checkout-button" data-funding-source="foo"></div>' +
      '</div>';

    const HTMLElement = document.getElementById('pay-with-payment-option-foo-form');

    const diContainer = buildDIContainerMock();
    const fundingSource = {
      name: 'foo',
      mark: { render: jest.fn() }
    };

    const component = new PaymentFieldsComponent(diContainer, {
      fundingSource,
      HTMLElement
    });

    expect(component.render()).toBe(component);
    expect(HTMLElement.classList.contains('ps_checkout-payment-fields')).toBeTruthy();
    expect(HTMLElement.getAttribute('data-funding-source')).toBe('foo');
  });
});
