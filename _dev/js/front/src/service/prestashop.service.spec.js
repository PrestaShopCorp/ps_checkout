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

// TODO: Refactor this class to use DIContainer
function buildDIContainerMock() {
  return {};
}

describe('src/service/prestashop.service.spec.js', () => {
  test('::getVersion() returns PS_VERSION_1_6', () => {
    const psService = new PrestashopService();
    expect(psService.getVersion()).toBe(PS_VERSION_1_6);
  });

  test('::getVersion() returns PS_VERSION_1_7', () => {
    window.prestashop = {};

    const psService = new PrestashopService();
    expect(psService.getVersion()).toBe(PS_VERSION_1_7);

    delete window.prestashop;
  });
});
