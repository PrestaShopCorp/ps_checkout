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
import { PsCheckoutService } from './ps-checkout.service';
import { PsCheckoutApi } from '../api/ps-checkout.api';
import { PayPalSdkConfig } from '../config/paypal-sdk.config';

jest.mock('../api/ps-checkout.api');
jest.mock('../config/paypal-sdk.config');

function buildDIContainerMock() {
  return {
    container: {
      PsCheckoutApi: new PsCheckoutApi(),
      PayPalSdkConfig: { ...PayPalSdkConfig },
      $: jest.fn()
    }
  };
}

describe('src/service/ps-checkout.service.spec.js', () => {
  test('::getPayPalToken() with client token in config', async () => {
    const diContainer = buildDIContainerMock();
    const service = new PsCheckoutService(diContainer);

    const clientToken = 'MyFooClientToken';
    diContainer.container.PayPalSdkConfig.clientToken = clientToken;

    expect(await service.getPayPalToken()).toBe(clientToken);
  });

  test('::getPayPalToken() without client token in config', async () => {
    const diContainer = buildDIContainerMock();
    const service = new PsCheckoutService(diContainer);

    const clientToken = 'MyFooClientToken';
    diContainer.container.PsCheckoutApi.getGetToken.mockResolvedValueOnce(
      clientToken
    );

    expect(diContainer.container.PayPalSdkConfig.clientToken).toBeFalsy();
    expect(await service.getPayPalToken()).toBe(clientToken);
  });

  test('::getPayPalToken() without client token in config and api error', () => {
    const diContainer = buildDIContainerMock();
    const service = new PsCheckoutService(diContainer);

    diContainer.container.PsCheckoutApi.getGetToken.mockRejectedValueOnce();

    expect(diContainer.container.PayPalSdkConfig.clientToken).toBeFalsy();
    expect(() => service.getPayPalToken()).rejects.toBeUndefined();
  });
});
