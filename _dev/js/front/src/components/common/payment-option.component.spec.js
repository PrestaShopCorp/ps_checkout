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

import { HostedFieldsComponentMock } from '../../../test/mocks/components/common/hosted-fields.component.mock';
import { MarkComponentMock } from '../../../test/mocks/components/common/mark.component.mock';
import { SmartButtonComponentMock } from '../../../test/mocks/components/common/smart-button.component.mock';

function buildDIContainerMock() {
  return {
    container: {
      PsCheckoutConfig: {
        ...PsCheckoutConfig
      },
      $: jest.fn().mockImplementation(key => {
        return (
          {
            'funding-source.name.foo': 'Foo',
            'funding-source.name.default': 'Default'
          }[key] || ''
        );
      })
    }
  };
}

describe('src/components/common/payment-option.component.spec.js', () => {
  const markComponentMock = MarkComponentMock;
  const smartButtonComponentMock = SmartButtonComponentMock;
  const hostedFieldsComponentMock = HostedFieldsComponentMock;

  let PaymentOptionComponent;

  beforeEach(() => {
    markComponentMock.mockClear();
    markComponentMock.render.mockClear();

    smartButtonComponentMock.mockClear();
    smartButtonComponentMock.render.mockClear();

    hostedFieldsComponentMock.mockClear();
    hostedFieldsComponentMock.render.mockClear();

    jest
      .doMock('./marker.component', () => ({
        MarkComponent: markComponentMock
      }))
      .doMock('./smart-button.component', () => ({
        SmartButtonComponent: smartButtonComponentMock
      }))
      .doMock('./hosted-fields.component', () => ({
        HostedFieldsComponent: hostedFieldsComponentMock
      }));

    PaymentOptionComponent = require('./payment-option.component')
      .PaymentOptionComponent;
  });

  afterEach(() => {
    jest.clearAllMocks();

    return (document.body.innerHTML = '');
  });

  test('::render() with SmartButton and translatable label', () => {
    document.body.innerHTML =
      '<div id="foo-payment-option-container">' +
      ' <div id="foo-label">Foo</div>' +
      ' <div id="foo-payment-option"></div>' +
      ' <div class="ps_checkout-button" data-funding-source="foo"></div>' +
      '</div>';

    const HTMLElement = document.getElementById('foo-payment-option');
    const fundingSource = {
      name: 'foo'
    };

    const diContainer = buildDIContainerMock();
    const paymentOptionComponent = new PaymentOptionComponent(diContainer, {
      HTMLElement,
      fundingSource
    });

    const labelListener = jest.fn();
    paymentOptionComponent.onLabelClick(labelListener);

    expect(paymentOptionComponent.render()).toBe(paymentOptionComponent);
    expect(markComponentMock.render).toHaveBeenCalledTimes(1);
    expect(smartButtonComponentMock.render).toHaveBeenCalledTimes(1);
    expect(hostedFieldsComponentMock.render).not.toHaveBeenCalled();
    expect(diContainer.container.$).toHaveBeenCalledTimes(2);

    expect(labelListener).not.toHaveBeenCalled();
    document.getElementById('foo-label').click();
    expect(labelListener).toHaveBeenCalledTimes(1);
  });
});
