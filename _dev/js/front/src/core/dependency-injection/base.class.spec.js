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
import { DI_CONTAINER } from '../../../test/mocks/di-container.mock';

describe('src/core/dependency-injection/base-class.spec.js', () => {
  let BaseClass;

  let inject;
  let injector;

  beforeAll(() => {
    injector = jest.fn();
    inject = jest.fn().mockReturnValue(injector);

    jest.doMock('../../utils/dependency-injection/inject', () => {
      return {
        __esModule: true,
        inject
      };
    });

    return import('./base.class').then(({ BaseClass: BaseClassModule }) => {
      BaseClass = BaseClassModule;
    });
  });

  afterAll(() => {
    jest.resetModules();
  });

  test('Inject is being called', () => {
    new BaseClass(DI_CONTAINER);
    expect(inject).toHaveBeenCalled();
  });
});
