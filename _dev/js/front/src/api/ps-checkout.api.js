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
import { BaseClass } from '../core/dependency-injection/base.class';

export class PsCheckoutApi extends BaseClass {
  static Inject = {
    config: 'PsCheckoutConfig',
    $: '$'
  };

  postCancelOrder(data) {
    return fetch(this.config.cancelUrl, {
      method: 'post',
      credentials: 'same-origin',
      headers: {
        'Content-Type': 'application/json',
        Accept: 'application/json'
      },
      body: JSON.stringify(data)
    }).then((response) => {
      const contentType = response.headers.get('content-type');
      const isJsonResponse =
        contentType && contentType.indexOf('application/json') !== -1;

      if (false === response.ok) {
        if (isJsonResponse) {
          return response.json().then((response) => {
            throw response.body && response.body.error
              ? response.body.error
              : { message: this.$('checkout.form.error.label') };
          });
        }

        throw new Error(this.$('checkout.form.error.label'));
      }
    });
  }

  postCheckCartOrder(data, actions) {
    return this.config.orderId
      ? fetch(this.config.checkCartUrl, {
          method: 'post',
          credentials: 'same-origin',
          headers: {
            'Content-Type': 'application/json',
            Accept: 'application/json'
          },
          body: JSON.stringify(data)
        })
          .then((response) => {
            const contentType = response.headers.get('content-type');
            const isJsonResponse =
              contentType && contentType.indexOf('application/json') !== -1;

            if (isJsonResponse) {
              if (false === response.ok) {
                return response.json().then((response) => {
                  throw response.body && response.body.error
                    ? response.body.error
                    : { message: this.$('checkout.form.error.label') };
                });
              }

              return response.json();
            }

            throw new Error(this.$('checkout.form.error.label'));
          })
          .then((data) => {
            if (!data) {
              return actions.reject();
            } else {
              return actions.resolve();
            }
          })
      : Promise.resolve().then(() => actions.resolve());
  }

  /**
   * @param {*} [data]
   * @returns {Promise<any>}
   */
  postCreateOrder(data) {
    return fetch(this.config.createUrl, {
      method: 'post',
      credentials: 'same-origin',
      headers: {
        'Content-Type': 'application/json',
        Accept: 'application/json'
      },
      ...(data ? { body: JSON.stringify(data) } : {})
    })
      .then((response) => {
        const contentType = response.headers.get('content-type');
        const isJsonResponse =
          contentType && contentType.indexOf('application/json') !== -1;

        if (isJsonResponse) {
          if (false === response.ok || response.status >= 400) {
            return response.json().then((response) => {
              throw response.body && response.body.error
                ? response.body.error
                : { message: this.$('checkout.form.error.label') };
            });
          }

          return response.json();
        }

        throw new Error(this.$('checkout.form.error.label'));
      })
      .then(({ body: { orderID } }) => orderID);
  }

  postValidateOrder(data, actions) {
    return fetch(this.config.validateOrderUrl, {
      method: 'post',
      credentials: 'same-origin',
      headers: {
        'Content-Type': 'application/json',
        Accept: 'application/json'
      },
      body: JSON.stringify(data)
    })
      .then((response) => {
        const contentType = response.headers.get('content-type');
        const isJsonResponse =
          contentType && contentType.indexOf('application/json') !== -1;

        if (isJsonResponse) {
          if (false === response.ok || response.status >= 400) {
            return response.json().then((response) => {
              if (
                actions?.restart &&
                response.body &&
                85 === response.body.error.code
              ) {
                return actions.restart();
              }

              throw response.body && response.body.error
                ? response.body.error
                : { message: this.$('checkout.form.error.label') };
            });
          }

          return response.json();
        }

        throw new Error(this.$('checkout.form.error.label'));
      })
      .then((response) => {
        if (response.body && response.body.id_order) {
          const {
            id_cart,
            id_module,
            id_order,
            secure_key,
            paypal_order,
            paypal_transaction
          } = response.body;

          const confirmationUrl = new URL(this.config.confirmationUrl);
          confirmationUrl.searchParams.append('id_cart', id_cart);
          confirmationUrl.searchParams.append('id_module', id_module);
          confirmationUrl.searchParams.append('id_order', id_order);
          confirmationUrl.searchParams.append('key', secure_key);
          confirmationUrl.searchParams.append('paypal_order', paypal_order);
          confirmationUrl.searchParams.append(
            'paypal_transaction',
            paypal_transaction
          );

          window.location.href = confirmationUrl.toString();
        }
      });
  }

  postExpressCheckoutOrder(data, actions) {
    return actions.order.get().then(({ payer, purchase_units }) =>
      fetch(this.config.expressCheckoutUrl, {
        method: 'post',
        credentials: 'same-origin',
        headers: {
          'Content-Type': 'application/json',
          Accept: 'application/json'
        },
        body: JSON.stringify({
          ...data,
          order: {
            payer: payer,
            shipping: purchase_units[0].shipping
          }
        })
      }).then((response) => {
        const contentType = response.headers.get('content-type');
        const isJsonResponse =
          contentType && contentType.indexOf('application/json') !== -1;

        if (isJsonResponse) {
          if (false === response.ok || response.status >= 400) {
            return response.json().then((response) => {
              throw response.body && response.body.error
                ? response.body.error
                : { message: this.$('checkout.form.error.label') };
            });
          }

          window.location.href = new URL(
            this.config.checkoutCheckoutUrl
          ).toString();

          return;
        }

        throw new Error(this.$('checkout.form.error.label'));
      })
    );
  }
}
