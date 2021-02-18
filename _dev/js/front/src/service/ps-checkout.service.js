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

export class PsCheckoutService extends BaseClass {
  static Inject = {
    psCheckoutApi: 'PsCheckoutApi',
    payPalSdkConfig: 'PayPalSdkConfig',

    $: '$'
  };

  async getPayPalToken() {
    return this.payPalSdkConfig.clientToken
      ? Promise.resolve(this.payPalSdkConfig.clientToken)
      : await this.psCheckoutApi.getGetToken();
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

  /* istanbul ignore next */
  // TODO: Remove this method if finally is unneeded
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
}
