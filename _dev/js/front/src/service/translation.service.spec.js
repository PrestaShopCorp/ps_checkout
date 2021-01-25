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
import { TranslationService } from './translation.service';

function buildDIContainerMock() {
  return {
    container: {
      PsCheckoutConfig: {
        translations: {
          foo: 'Foo',
          bar: 'Bar'
        }
      }
    }
  };
}

describe('src/service/translation.service.spec.js', () => {
  test('::getTranslationString() with existing id', async () => {
    const diContainer = buildDIContainerMock();
    const service = new TranslationService(diContainer);

    expect(service.getTranslationString('foo')).toBe('Foo');
    expect(service.getTranslationString('bar')).toBe('Bar');
  });

  test('::getTranslationString() with missing id', async () => {
    const diContainer = buildDIContainerMock();
    const service = new TranslationService(diContainer);

    expect(service.getTranslationString('baz')).toBe('TRANSLATED_STRING(baz)');
  });
});
