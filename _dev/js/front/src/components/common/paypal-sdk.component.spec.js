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
import { PayPalSdkConfig } from '../../config/paypal-sdk.config';
import { PayPalSdkComponent } from './paypal-sdk.component';
import * as PrestashopSite1_7 from '../../../test/mocks/html-templates/prestashop-site-1_7';

function buildDIContainerMock() {
  PrestashopSite1_7.mockCheckoutVars();
  const payPalSdkConfig = new PayPalSdkConfig();
  return {
    container: {
      PayPalSdkConfig: {
        ...payPalSdkConfig,
        id: 'foo',
        src: 'url',
        namespace: 'fooNamespace'
      }
    }
  };
}

describe('src/components/common/paypal-sdk.component.spec.js', () => {
  afterEach(() => (document.head.innerHTML = ''));

  test('::render()', () => {
    const diContainer = buildDIContainerMock();

    const payPalSdkComponent = new PayPalSdkComponent(diContainer);
    expect(payPalSdkComponent.render()).toBe(payPalSdkComponent);

    const payPalScript = document.head.querySelector('script');
    expect(payPalScript.hasAttribute('async')).toBeTruthy();
    expect(payPalScript.getAttribute('id')).toBe(
      diContainer.container.PayPalSdkConfig.id
    );
    expect(payPalScript.getAttribute('src')).toBe(
      diContainer.container.PayPalSdkConfig.src
    );
    expect(payPalScript.getAttribute('data-namespace')).toBe(
      diContainer.container.PayPalSdkConfig.namespace
    );

    expect(payPalScript.hasAttribute('data-enable-3ds')).toBeFalsy();
    expect(payPalScript.hasAttribute('data-csp-nonce')).toBeFalsy();
    expect(payPalScript.hasAttribute('data-order-id')).toBeFalsy();
  });

  test('::render() with 3DS', () => {
    const diContainer = buildDIContainerMock();
    diContainer.container.PayPalSdkConfig.card3dsEnabled = true;

    const payPalSdkComponent = new PayPalSdkComponent(diContainer);
    expect(payPalSdkComponent.render()).toBe(payPalSdkComponent);

    const payPalScript = document.head.querySelector('script');
    expect(payPalScript.hasAttribute('data-enable-3ds')).toBeTruthy();
  });

  test('::render() with CSPNonce', () => {
    const diContainer = buildDIContainerMock();
    diContainer.container.PayPalSdkConfig.cspNonce = 'Baz';

    const payPalSdkComponent = new PayPalSdkComponent(diContainer);
    expect(payPalSdkComponent.render()).toBe(payPalSdkComponent);

    const payPalScript = document.head.querySelector('script');
    expect(payPalScript.getAttribute('data-csp-nonce')).toBe(
      diContainer.container.PayPalSdkConfig.cspNonce
    );
  });

  test('::render() with Order Id', () => {
    const diContainer = buildDIContainerMock();
    diContainer.container.PayPalSdkConfig.orderId = 'Baz';

    const payPalSdkComponent = new PayPalSdkComponent(diContainer);
    expect(payPalSdkComponent.render()).toBe(payPalSdkComponent);

    const payPalScript = document.head.querySelector('script');
    expect(payPalScript.getAttribute('data-order-id')).toBe(
      diContainer.container.PayPalSdkConfig.orderId
    );
  });

  test('::render() and Resolves', () => {
    const diContainer = buildDIContainerMock();
    window[diContainer.container.PayPalSdkConfig.namespace] = 'sdk';

    const payPalSdkComponent = new PayPalSdkComponent(diContainer);
    expect(payPalSdkComponent.render()).toBe(payPalSdkComponent);

    const payPalScript = document.head.querySelector('script');
    payPalScript.onload();

    return payPalSdkComponent.promise.then(() => {
      expect(payPalSdkComponent.sdk).toBe('sdk');
      delete window[diContainer.container.PayPalSdkConfig.namespace];
    });
  });

  test('::render() and Reject', () => {
    const diContainer = buildDIContainerMock();

    const payPalSdkComponent = new PayPalSdkComponent(diContainer);
    expect(payPalSdkComponent.render()).toBe(payPalSdkComponent);

    const payPalScript = document.head.querySelector('script');
    payPalScript.onerror();

    let rejects = false;
    return payPalSdkComponent.promise
      .catch(() => (rejects = true))
      .then(() => {
        if (!rejects) {
          throw new Error('Promise should reject');
        }
      });
  });

  test('::render() with new Sdk', () => {
    const diContainer = buildDIContainerMock();
    const payPalSdkComponent = new PayPalSdkComponent(diContainer);

    expect(payPalSdkComponent.render()).toBe(payPalSdkComponent);

    return payPalSdkComponent.promise.then(() => {
      expect(payPalSdkComponent.sdk).toBeDefined();
    });
  });
});
