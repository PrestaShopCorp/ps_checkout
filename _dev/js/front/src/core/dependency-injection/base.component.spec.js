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
import { BaseComponent } from './base.component';

import { DI_CONTAINER } from '../../../test/mocks/di-container.mock';

describe.only('src/core/dependency-injection/base-component.spec.js', () => {
  test('Simple Initialization', () => {
    let createdCalled = false;
    const cls = class extends BaseComponent {
      created() {
        createdCalled = true;
      }
    };

    const instance = new cls(DI_CONTAINER);
    expect(createdCalled).toBeTruthy();

    expect(instance.data).toMatchObject({});
    expect(instance.props).toMatchObject({});
    expect(instance.children).toMatchObject({});
  });

  test('Props Initialization', () => {
    const props = { foo: 'Foo', bar: 'Bar' };
    const cls = class extends BaseComponent {};

    const instance = new cls(DI_CONTAINER, props);
    expect(instance.props).toMatchObject(props);
  });

  test('Base Render', () => {
    const cls = class extends BaseComponent {};

    const instance = new cls(DI_CONTAINER);
    expect(instance.render()).toBe(instance);
  });
});
