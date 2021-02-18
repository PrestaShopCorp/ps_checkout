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
import { MarkComponent } from './marker.component';

function buildDIContainerMock() {
  return {
    container: {
      PsCheckoutConfig: {
        customMark: {
          bar: 'baz'
        }
      }
    }
  };
}

describe('src/components/common/marker.component.spec.js', () => {
  beforeEach(() => (document.body.innerHTML = `<div id="foo" />`));

  afterEach(() => (document.body.innerHTML = ''));

  test('::render() with custom mark', () => {
    const HTMLElement = document.getElementById('foo');
    const fundingSource = {
      name: 'bar',
      mark: { render: jest.fn() }
    };

    const diContainer = buildDIContainerMock();
    const component = new MarkComponent(diContainer, {
      fundingSource,
      HTMLElement
    });

    expect(component.render()).toBe(component);

    const image = document.querySelector('img');
    expect(image.classList.contains('ps-checkout-funding-img')).toBeTruthy();
    expect(image.getAttribute('alt')).toBe(fundingSource.name);
    expect(image.getAttribute('src')).toBe(
      diContainer.container.PsCheckoutConfig.customMark.bar
    );

    expect(HTMLElement.classList.contains('ps_checkout-mark')).toBeTruthy();
    expect(HTMLElement.getAttribute('data-funding-source')).toBe(
      fundingSource.name
    );
  });

  test('::render() with default mark', () => {
    const HTMLElement = document.getElementById('foo');
    const fundingSource = {
      name: 'foo',
      mark: { render: jest.fn() }
    };

    const selector = `.ps_checkout-mark[data-funding-source=${fundingSource.name}]`;

    const diContainer = buildDIContainerMock();
    const component = new MarkComponent(diContainer, {
      fundingSource,
      HTMLElement
    });

    expect(component.render()).toBe(component);

    expect(fundingSource.mark.render).toHaveBeenCalledWith(selector);

    expect(HTMLElement.classList.contains('ps_checkout-mark')).toBeTruthy();
    expect(HTMLElement.getAttribute('data-funding-source')).toBe(
      fundingSource.name
    );
  });
});
