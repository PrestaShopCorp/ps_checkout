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
import { LoaderComponent } from './loader.component';

function buildDIContainerMock() {
  return {
    container: {
      PsCheckoutConfig: {
        ...PsCheckoutConfig
      },
      QuerySelectorService: {
        getLoaderParent: jest.fn()
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

describe('src/components/common/loader.component.spec.js', () => {
  afterEach(() => (document.body.innerHTML = ''));

  test('::render()', () => {
    const diContainer = buildDIContainerMock();
    diContainer.container.QuerySelectorService.getLoaderParent.mockImplementationOnce(
      () => document.body
    );

    const loaderComponent = new LoaderComponent(diContainer);
    expect(loaderComponent.render()).toBe(loaderComponent);

    const overlayHTMLElement = document.querySelector('.ps-checkout.overlay');
    expect(overlayHTMLElement.parentElement).toBe(document.body);

    const popupHTMLElement = document.querySelector('.ps-checkout.popup');
    expect(popupHTMLElement.parentElement).toBe(overlayHTMLElement);

    const popupTextHTMLElement = document.querySelector('.ps-checkout.text');
    expect(popupTextHTMLElement.parentElement).toBe(popupHTMLElement);

    const popupLoaderHTMLElement = document.querySelector(
      '.ps-checkout.loader'
    );
    expect(popupLoaderHTMLElement.parentElement).toBe(popupHTMLElement);

    const popupSubtextHTMLElement = document.querySelector(
      '.ps-checkout.subtext'
    );
    expect(popupSubtextHTMLElement.parentElement).toBe(popupHTMLElement);
  });

  test('::show() ', () => {
    const diContainer = buildDIContainerMock();
    diContainer.container.QuerySelectorService.getLoaderParent.mockImplementationOnce(
      () => document.body
    );

    const loaderComponent = new LoaderComponent(diContainer);
    expect(loaderComponent.render()).toBe(loaderComponent);

    const overlayHTMLElement = document.querySelector('.ps-checkout.overlay');
    expect(overlayHTMLElement.parentElement).toBe(document.body);
    expect(overlayHTMLElement.classList).not.toContain('visible');

    loaderComponent.show();
    expect(overlayHTMLElement.classList).toContain('visible');
  });

  test('::hide() ', () => {
    const diContainer = buildDIContainerMock();
    diContainer.container.QuerySelectorService.getLoaderParent.mockImplementationOnce(
      () => document.body
    );

    const loaderComponent = new LoaderComponent(diContainer);
    expect(loaderComponent.render()).toBe(loaderComponent);

    const overlayHTMLElement = document.querySelector('.ps-checkout.overlay');
    expect(overlayHTMLElement.parentElement).toBe(document.body);
    expect(overlayHTMLElement.classList).not.toContain('visible');

    loaderComponent.show();
    expect(overlayHTMLElement.classList).toContain('visible');

    loaderComponent.hide();
    expect(overlayHTMLElement.classList).not.toContain('visible');
  });
});
