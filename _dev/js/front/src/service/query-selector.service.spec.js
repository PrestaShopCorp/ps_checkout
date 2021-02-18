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
import { QuerySelectorService } from './query-selector.service';
import { PrestashopService } from './prestashop.service';

function buildDIContainerMock() {
  return {
    container: {
      PrestashopService: new PrestashopService()
    }
  };
}

describe('src/service/query-selector.service.spec.js', () => {
  const testAllQSSMethods = (qss) => {
    expect(() => qss.getBasePaymentConfirmation()).not.toThrow();
    expect(() => qss.getConditionsCheckboxes()).not.toThrow();
    expect(() => qss.getLoaderParent()).not.toThrow();
    expect(() => qss.getNotificationConditions()).not.toThrow();
    expect(() => qss.getNotificationPaymentCanceled()).not.toThrow();
    expect(() => qss.getNotificationPaymentError()).not.toThrow();
    expect(() => qss.getNotificationPaymentErrorText()).not.toThrow();
    expect(() => qss.getPaymentOptions()).not.toThrow();
    expect(() => qss.getPaymentOptionsLoader()).not.toThrow();
    expect(() => qss.getPaymentOptionRadios()).not.toThrow();
  };

  test('All methods are defined for PS1.6', () => {
    const qss = new QuerySelectorService(buildDIContainerMock());
    testAllQSSMethods(qss);
  });

  test('All methods are defined for PS1.7', () => {
    window.prestashop = {};

    const qss = new QuerySelectorService(buildDIContainerMock());
    testAllQSSMethods(qss);

    delete window.prestashop;
  });
});
