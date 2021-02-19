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
import { PaymentOptionsLoaderComponent } from './payment-options-loader.component';

function buildDIContainerMock() {
  return {
    container: {
      QuerySelectorService: {
        getPaymentOptionsLoader: jest.fn()
      }
    }
  };
}

describe('src/components/common/payment-options-loader.component.spec.js', () => {
  test('::render() without HTMLElement', () => {
    const diContainer = buildDIContainerMock();
    diContainer.container.QuerySelectorService.getPaymentOptionsLoader.mockReturnValueOnce(
      undefined
    );

    const component = new PaymentOptionsLoaderComponent(diContainer);
    expect(component.render()).toBe(component);
  });

  test('::render()', () => {
    document.body.innerHTML = '<div id="foo" />';

    const diContainer = buildDIContainerMock();
    diContainer.container.QuerySelectorService.getPaymentOptionsLoader.mockReturnValueOnce(
      document.getElementById('foo')
    );

    const component = new PaymentOptionsLoaderComponent(diContainer);
    expect(component.render()).toBe(component);

    document.body.innerHTML = '';
  });

  test('::hide() without HTMLElement', () => {
    const diContainer = buildDIContainerMock();
    diContainer.container.QuerySelectorService.getPaymentOptionsLoader.mockReturnValueOnce(
      undefined
    );

    const component = new PaymentOptionsLoaderComponent(diContainer);
    expect(component.render()).toBe(component);
    expect(() => component.hide()).not.toThrow();
  });

  test('::hide()', () => {
    document.body.innerHTML = '<div id="foo" />';
    const loader = document.getElementById('foo');

    const diContainer = buildDIContainerMock();
    diContainer.container.QuerySelectorService.getPaymentOptionsLoader.mockReturnValueOnce(
      loader
    );

    const component = new PaymentOptionsLoaderComponent(diContainer);
    expect(component.render()).toBe(component);
    expect(() => component.hide()).not.toThrow();
    expect(loader.style.display).toBe('none');
  });
});
