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
const onMessage = (eventSource, store) => event => {
  const { data: dataString } = event;
  eventSource.close();

  store.dispatch({
    type: 'sendSseOnboardingWebhook',
    data: JSON.parse(dataString)
  });
};

const onError = eventSource => event => {
  const { type, data } = event;
  if (type === 'error' && !data) {
    eventSource.close();
  }
};

export class SseOnboardingService {
  eventSource;
  store;

  constructor(store) {
    this.store = store;
  }

  open() {
    const { prestashopCheckoutSse } = this.store.state.context;
    const { idToken } = this.store.state.firebase;

    if (idToken) {
      const sseUrl = `${prestashopCheckoutSse}?access_token=${idToken}`;
      this.eventSource = new EventSource(sseUrl);

      this.eventSource.onmessage = onMessage(this.eventSource, this.store);
      this.eventSource.onerror = onError(this.eventSource, this.store);
    } else {
      this.eventSource = null;
    }
  }

  close() {
    if (this.eventSource) {
      this.eventSource.close();
    }
  }
}
