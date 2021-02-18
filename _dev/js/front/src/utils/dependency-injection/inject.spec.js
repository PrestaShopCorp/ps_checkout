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
import { BaseClass } from '../../core/dependency-injection/base.class';

import { DI_CONTAINER } from '../../../test/mocks/di-container.mock';

describe('src/utils/dependency-injection/inject.spec.js', () => {
  test('No injection', () => {
    let cls = class extends BaseClass {};
    expect(() => new cls(DI_CONTAINER)).not.toThrow();
  });

  test('Single service injection with one level of inheritance', () => {
    let cls = class extends BaseClass {
      static Inject = {
        foo: 'ServiceFoo'
      };
    };

    let instance = new cls(DI_CONTAINER);
    expect(instance.foo).toBe('Foo');
  });

  test('Multiple service injection with one level of inheritance', () => {
    let cls = class extends BaseClass {
      static Inject = {
        foo: 'ServiceFoo',
        bar: 'ServiceBar',
        baz: 'ServiceBaz'
      };
    };

    let instance = new cls(DI_CONTAINER);
    expect(instance.foo).toBe('Foo');
    expect(instance.bar).toBe('Bar');
    expect(instance.baz).toBe('Baz');
  });

  test('Multiple service injection with multiple level of inheritance', () => {
    let cls1 = class extends BaseClass {
      static Inject = {
        foo: 'ServiceFoo'
      };
    };

    let cls2 = class extends cls1 {
      static Inject = {
        bar: 'ServiceBar',
        baz: 'ServiceBaz'
      };
    };

    let instance = new cls2(DI_CONTAINER);
    expect(instance.foo).toBe('Foo');
    expect(instance.bar).toBe('Bar');
    expect(instance.baz).toBe('Baz');
  });
});
