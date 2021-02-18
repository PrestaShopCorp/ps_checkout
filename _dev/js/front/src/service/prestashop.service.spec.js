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
import { PrestashopService } from './prestashop.service';
import {
  PS_VERSION_1_6,
  PS_VERSION_1_7
} from '../constants/ps-version.constants';

import * as PrestashopSite1_7 from '../../test/mocks/html-templates/prestashop-site-1_7';
import PRODUCT_DATASET from '../../test/mocks/data/product-dataset.json';

// TODO: Refactor this class to use DIContainer
function buildDIContainerMock() {
  return {};
}

describe('src/service/prestashop.service.spec.js', () => {
  describe('PS_VERSION_1_6', () => {
    beforeEach(() => (window.updatePaymentMethods = jest.fn()));

    afterEach(() => delete window.updatePaymentMethods);

    test('::getVersion() returns PS_VERSION_1_6', () => {
      const psService = new PrestashopService();
      expect(psService.getVersion()).toBe(PS_VERSION_1_6);
    });

    // This method only works on 1.6 so listener will never be called
    test("::onUpdatedCart() don't get called", () => {
      const listener = jest.fn();
      const psService = new PrestashopService();

      psService.onUpdatedCart(listener);
      expect(listener).not.toHaveBeenCalled();
    });

    test("::onUpdatePaymentMethods() don't get called if event is undefined", () => {
      const listener = jest.fn();

      delete window.updatePaymentMethods;

      const psService = new PrestashopService();

      psService.onUpdatePaymentMethods(listener);
      expect(listener).not.toHaveBeenCalled();
    });

    test('::onUpdatePaymentMethods()', () => {
      const listener = jest.fn();
      const psService = new PrestashopService();

      psService.onUpdatePaymentMethods(listener);
      window['updatePaymentMethods']();

      expect(listener).toHaveBeenCalled();
    });
  });

  describe('PS_VERSION_1_7', () => {
    beforeEach(
      () =>
        (window.prestashop = {
          on: jest.fn()
        })
    );

    afterEach(() => delete window.prestashop);

    test('::getProductDetails()', () => {
      PrestashopSite1_7.mockProductPage();

      const psService = new PrestashopService();
      expect(psService.getProductDetails()).toEqual(PRODUCT_DATASET);

      PrestashopSite1_7.cleanSite();
    });

    test('::isCartPage()', () => {
      PrestashopSite1_7.mockCartPage();

      const psService = new PrestashopService();
      expect(psService.isCartPage()).toBeTruthy();

      PrestashopSite1_7.cleanSite();
    });

    test('::isOrderPaymentStepPage() on wrong page', () => {
      PrestashopSite1_7.mockProductPage();

      const psService = new PrestashopService();
      expect(psService.isOrderPaymentStepPage()).toBeFalsy();

      PrestashopSite1_7.cleanSite();
    });

    test('::isOrderPaymentStepPage() on wrong step', () => {
      PrestashopSite1_7.mockCheckoutPage();

      const psService = new PrestashopService();
      expect(psService.isOrderPaymentStepPage()).toBeFalsy();

      PrestashopSite1_7.cleanSite();
    });

    test('::isOrderPaymentStepPage()', () => {
      PrestashopSite1_7.mockCheckoutPaymentStepPage();

      const psService = new PrestashopService();
      expect(psService.isOrderPaymentStepPage()).toBeTruthy();

      PrestashopSite1_7.cleanSite();
    });

    test('::isOrderPersonalInformationStepPage() on wrong page', () => {
      PrestashopSite1_7.mockProductPage();

      const psService = new PrestashopService();
      expect(psService.isOrderPersonalInformationStepPage()).toBeFalsy();

      PrestashopSite1_7.cleanSite();
    });

    test('::isOrderPersonalInformationStepPage() on wrong step', () => {
      PrestashopSite1_7.mockCheckoutPage();

      const psService = new PrestashopService();
      expect(psService.isOrderPersonalInformationStepPage()).toBeFalsy();

      PrestashopSite1_7.cleanSite();
    });

    test('::isOrderPersonalInformationStepPage()', () => {
      PrestashopSite1_7.mockCheckoutPersonalInformationStepPage();

      const psService = new PrestashopService();
      expect(psService.isOrderPersonalInformationStepPage()).toBeTruthy();

      PrestashopSite1_7.cleanSite();
    });

    test('::isProductPage()', () => {
      PrestashopSite1_7.mockProductPage();

      const psService = new PrestashopService();
      expect(psService.isProductPage()).toBeTruthy();

      PrestashopSite1_7.cleanSite();
    });

    test('::getVersion() returns PS_VERSION_1_7', () => {
      const psService = new PrestashopService();
      expect(psService.getVersion()).toBe(PS_VERSION_1_7);
    });

    test('::onUpdatedCart() errors if prestashop object is wrong', () => {
      const listener = jest.fn();

      const source = console.error;
      console.error = jest.fn();

      delete window.prestashop.on;

      const psService = new PrestashopService();

      psService.onUpdatedCart(listener);
      expect(console.error).toHaveBeenCalledTimes(1);
      expect(listener).not.toHaveBeenCalled();

      delete window.prestashop;

      psService.onUpdatedCart(listener);
      expect(console.error).toHaveBeenCalledTimes(2);
      expect(listener).not.toHaveBeenCalled();

      console.error = source;
    });

    test('::onUpdatedCart()', () => {
      const listener = jest.fn();
      const psService = new PrestashopService();

      psService.onUpdatedCart(listener);
      expect(window.prestashop.on).toHaveBeenCalledWith(
        'updatedCart',
        listener
      );
    });

    // This method only works on 1.6 so listener will never be called
    test("::onUpdatePaymentMethods() don't get called", () => {
      const listener = jest.fn();
      const psService = new PrestashopService();

      psService.onUpdatePaymentMethods(listener);
      expect(listener).not.toHaveBeenCalled();
    });
  });
});
