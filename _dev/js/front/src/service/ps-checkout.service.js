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
import {
  PS_VERSION_1_6,
  PS_VERSION_1_7
} from '../constants/ps-version.constants';

export class PsCheckoutService {
  constructor(config, translationService) {
    this.config = config;
    this.translationService = translationService;

    this.$ = (id) => this.translationService.getTranslationString(id);
  }

  isUserLogged() {
    return window.prestashop.customer.is_logged;
  }

  getProductDetails() {
    return JSON.parse(
      document.getElementById('product-details').dataset.product
    );
  }

  postCancelOrder(data) {
    return fetch(this.config.cancelUrl, {
      method: 'post',
      headers: {
        'content-type': 'application/json'
      },
      body: JSON.stringify(data)
    }).then((response) => {
      if (false === response.ok) {
        return response.json().then((response) => {
          throw response.body && response.body.error
            ? response.body.error
            : { message: 'Unknown error' };
        });
      }
    });
  }

  postCheckCartOrder(data, actions) {
    return this.config.orderId
      ? fetch(this.config.checkCartUrl, {
          method: 'post',
          headers: {
            'content-type': 'application/json'
          },
          body: JSON.stringify(data)
        })
          .then((response) => {
            if (false === response.ok) {
              return response.json().then((response) => {
                throw response.body && response.body.error
                  ? response.body.error
                  : { message: 'Unknown error' };
              });
            }

            return response.json();
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
      headers: {
        'content-type': 'application/json'
      },
      ...(data ? { body: JSON.stringify(data) } : {})
    })
      .then((response) => {
        if (false === response.ok) {
          return response.json().then((response) => {
            throw response.body && response.body.error
              ? response.body.error
              : { message: 'Unknown error' };
          });
        }

        return response.json();
      })
      .then(({ body: { orderID } }) => orderID);
  }

  postGetToken() {
    return fetch(this.config.getTokenUrl, {
      method: 'post',
      headers: {
        'content-type': 'application/json'
      }
    })
      .then((response) => {
        if (false === response.ok) {
          return response.json().then((response) => {
            throw response.body && response.body.error
              ? response.body.error
              : { message: 'Unknown error' };
          });
        }

        return response.json();
      })
      .then(({ body: { token } }) => token);
  }

  postValidateOrder(data, actions) {
    return fetch(this.config.validateOrderUrl, {
      method: 'post',
      headers: {
        'content-type': 'application/json'
      },
      body: JSON.stringify(data)
    })
      .then((response) => {
        if (false === response.ok) {
          return response.json().then((response) => {
            throw response.body && response.body.error
              ? response.body.error
              : { message: 'Unknown error' };
          });
        }

        return response.json();
      })
      .then((response) => {
        if (response.body && 'COMPLETED' === response.body.paypal_status) {
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

        if (response.error && 'INSTRUMENT_DECLINED' === response.error) {
          return actions.restart();
        }
      });
  }

  postExpressCheckoutOrder(data, actions) {
    return actions.order.get().then(({ payer, purchase_units }) =>
      fetch(this.config.expressCheckoutUrl, {
        method: 'post',
        headers: {
          'content-type': 'application/json'
        },
        body: JSON.stringify({
          ...data,
          order: {
            payer: payer,
            shipping: purchase_units[0].shipping
          }
        })
      }).then((response) => {
        if (false === response.ok) {
          return response.json().then((response) => {
            throw response.body && response.body.error
              ? response.body.error
              : { message: 'Unknown error' };
          });
        }

        window.location.href = new URL(
          this.config.checkoutCheckoutUrl
        ).toString();
      })
    );
  }

  validateContingency(liabilityShifted, authenticationReason) {
    // No 3DS Contingency Passed or card not enrolled to 3ds
    if (undefined === liabilityShifted) {
      return Promise.resolve();
    }

    // 3DS Contingency Passed - Buyer confirmed Successfully
    if (true === liabilityShifted) {
      return Promise.resolve();
    }

    // 3DS Contingency Passed, but liabilityShifted must be checked
    if (false === liabilityShifted) {
      switch (authenticationReason) {
        case 'SUCCESSFUL':
          // Buyer successfully authenticated using 3D Secure
          return Promise.resolve();
        case 'CARD_INELIGIBLE':
          // Card is not eligible for 3D Secure authentication
          // Continue with authorization as authentication is not required
          return Promise.resolve();
        case 'ATTEMPTED':
          // Card is not enrolled in 3D Secure.
          // Card issuing bank is not participating in 3D Secure
          // Continue with authorization as authentication is not required
          return Promise.resolve();
        case 'BYPASSED':
          // Buyer may have failed the challenge or the device was not verified
          // You can continue with the authorization and assume liability.
          // If you prefer not to assume liability, ask the buyer for another card
          return Promise.resolve();
        case 'UNAVAILABLE':
          // Issuing bank is not able to complete authentication
          // You can continue with the authorization and assume liability.
          // If you prefer not to assume liability, ask the buyer for another card
          return Promise.resolve();
        case 'SKIPPED_BY_BUYER':
          // Buyer was presented the 3D Secure challenge but chose to skip the authentication
          // Do not continue with current authorization.
          // Prompt the buyer to re-authenticate or request buyer for another form of payment
          return Promise.reject(
            new Error(this.$('error.paypal-sdk.contingency.cancel'))
          );
        case 'ERROR':
          // An error occurred with the 3D Secure authentication system
          // Prompt the buyer to re-authenticate or request for another form of payment
          return Promise.reject(
            new Error(this.$('error.paypal-sdk.contingency.error'))
          );
        case 'FAILURE':
          // Buyer may have failed the challenge or the device was not verified
          // Do not continue with current authorization.
          // Prompt the buyer to re-authenticate or request buyer for another form of payment
          return Promise.reject(
            new Error(this.$('error.paypal-sdk.contingency.failure'))
          );
        default:
          return Promise.reject(
            new Error(this.$('error.paypal-sdk.contingency.unknown'))
          );
      }
    }
  }

  validateLiablityShift(liabilityShift) {
    if (undefined === liabilityShift) {
      console.log('Hosted fields : Liability is undefined.');
      return Promise.resolve();
    }

    if (false === liabilityShift) {
      console.log('Hosted fields : Liability is false.');
      return Promise.reject(
        new Error(this.$('error.paypal-sdk.liability.false'))
      );
    }

    if ('Possible' === liabilityShift) {
      console.log('Hosted fields : Liability might shift to the card issuer.');
      return Promise.resolve();
    }

    if ('No' === liabilityShift) {
      console.log('Hosted fields : Liability is with the merchant.');
      return Promise.resolve();
    }

    if ('Unknown' === liabilityShift) {
      console.log(
        'Hosted fields : The authentication system is not available.'
      );
      return Promise.resolve();
    }

    if (liabilityShift) {
      console.log('Hosted fields : Liability might shift to the card issuer.');
      return Promise.resolve();
    }

    console.log('Hosted fields : Liability unknown.');
    return Promise.reject(
      new Error(this.$('error.paypal-sdk.liability.unknown'))
    );
  }

  static getPrestashopVersion() {
    if (!window.prestashop) {
      return PS_VERSION_1_6;
    }

    return PS_VERSION_1_7;
  }
}
