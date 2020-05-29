/**
 * 2007-2020 PrestaShop and Contributors
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License 3.0 (AFL-3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2020 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 * International Registered Trademark & Property of PrestaShop SA
 */

document.addEventListener('DOMContentLoaded', () => {
  const interval = setInterval(() => {
    if (undefined !== window.paypalSdkPsCheckout) {
      initPsCheckout();
      clearInterval(interval);
    }
  }, 200);
});

function initPsCheckout() {
  if (undefined === paypalOrderId) {
    throw new Error('No paypal order id');
  }

  initHostedFields();
}

function initHostedFields() {
  // check whether hosted fields is eligible for that Partner Account
  if (paypalSdkPsCheckout.HostedFields.isEligible()) {
    // render hosted fields
    paypalSdkPsCheckout.HostedFields.render({
      createOrder() {
        return paypalOrderId;
      },
      styles: {
        input: {
          height: '25px',
          'font-size': '1rem',
        },
        ':focus': {
          'border-color': 'red',
        },
        'input.invalid': {
          color: '#c05c67',
        },
      },
      fields: {
        number: {
          selector: '#card-number',
          placeholder: cardNumberPlaceholder,
          class: 'form-control',
        },
        cvv: {
          selector: '#cvv',
          placeholder: cvvPlaceholder,
        },
        expirationDate: {
          selector: '#expiration-date',
          placeholder: expDatePlaceholder,
        },
      },
    }).then((hostedFields) => {
      hostedFields.on('cardTypeChange', (event) => {
        // Change card bg depending on card type
        if (event.cards.length === 1) {
          document.querySelector('.defautl-credit-card').style.display = 'none';

          const cardImage = document.getElementById('card-image');
          cardImage.className = '';
          cardImage.classList.add(event.cards[0].type);

          document.querySelector('header').classList.add('header-slide');

          // Change the CVV length for AmericanExpress cards
          if (event.cards[0].code.size === 4) {
            hostedFields.setAttribute({
              field: 'cvv',
              attribute: 'placeholder',
              value: 'XXXX',
            });
          }
        } else {
          document.querySelector('.defautl-credit-card').style.display = 'block';
          const cardImage = document.getElementById('card-image');
          cardImage.className = '';

          hostedFields.setAttribute({
            field: 'cvv',
            attribute: 'placeholder',
            value: 'XXX',
          });
        }
      });

      document.querySelector('#hosted-fields-validation').addEventListener('click', (event) => {
        event.preventDefault();
        toggleLoader(true);

        hostedFields.submit({
          contingencies: ['3D_SECURE'], // only necessary if using 3D Secure verification
        }).then((payload) => {
          // No 3DS Contingency Passed or card not enrolled to 3ds
          if (undefined === payload.liabilityShifted) {
            window.location.replace(validateOrderLinkByCard);
          }

          // 3DS Contingency Passed - Buyer confirmed Successfully
          if (true === payload.liabilityShifted) {
            window.location.replace(validateOrderLinkByCard);
          }

          // 3DS Contingency Passed, but Buyer skipped 3DS
          if (false === payload.liabilityShifted) {
            switch (payload.authenticationReason) {
              case 'ERROR':
              case 'SKIPPED_BY_BUYER':
              case 'FAILURE':
                displayCardError('3DS_' + payload.authenticationReason);
                break;
              default:
                window.location.replace(validateOrderLinkByCard);
            }
          }
        }).catch((err) => {
          if (undefined !== err.details && undefined !== err.details[0] && undefined !== err.details[0].issue) {
            displayCardError(err.details[0].issue);
            return;
          }

          displayCardError('UNKNOWN');
        });
      });
    });
  }
}

function displayCardError(err) {
  const displayError = document.getElementById('hostedFieldsErrors');

  if (undefined === err || undefined === hostedFieldsErrors[err]) {
    err = 'UNKNOWN';
  }

  displayError.classList.remove('hide-paypal-error');
  displayError.textContent = hostedFieldsErrors[err];
  toggleLoader(false);
}

function toggleLoader(enable) {
  if (true === enable) {
    const span = document.createElement('span');
    span.classList.add('spinner-hosted-fields');
    document.querySelector('#cart_navigation').append(span);
    document.querySelector('#hosted-fields-validation').style.display = 'none';
  } else {
    document.querySelector('.spinner-hosted-fields').remove();
    document.querySelector('#hosted-fields-validation').style.display = 'block';
  }
}
