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
    if (window.paypalSdkPsCheckout !== undefined) {
      initPsCheckout();
      clearInterval(interval);
    }
  }, 200);
});

function initPsCheckout() {
  if (typeof paypalOrderId === 'undefined') {
    throw new Error('No paypal order id');
  }

  hostedFieldsErrors = JSON.parse(hostedFieldsErrors.replace(/&quot;/g, '"'));

  hideDefaultPaymentButtonIfPaypalIsChecked();

  if (cardIsActive) {
    initHostedFields();
  }
  if (paypalIsActive) {
    initSmartButtons();

    // pre select payment by card if there was an error previously
    const urlParams = new URLSearchParams(location.search);

    if (urlParams.has('hferror') && urlParams.get('hferror') === '1') {
      document.querySelectorAll('[data-module-name="ps_checkout_hostedFields"]')[0].click();
    }
  }
}

function initSmartButtons() {
  // remove "amp;" from the url
  const orderValidationLinkByPaypal = validateOrderLinkByPaypal.replace(/\amp;/g, '');

  paypalSdkPsCheckout.Buttons({
    style: {
      shape: 'pill',
      size: 'small',
    },
    onInit(data, actions) {
      // Disable the buttons
      actions.disable();
      // Listen for changes to the checkbox
      document.querySelector('.buttons-approve').addEventListener('change', (event) => {
        // Enable or disable the button when it is checked or unchecked
        if (event.target.checked) {
          actions.enable();
          document.querySelector('#paypal-approve-error').classList.add('hide-paypal-error');
        } else {
          actions.disable();
        }
      });
    },
    onClick() {
      // Show a validation error if the checkbox is not checked
      if (!document.querySelector('.buttons-approve').checked) {
        document.querySelector('#paypal-approve-error').classList.remove('hide-paypal-error');
      }
    },
    createOrder() {
      return paypalOrderId;
    },
    onApprove() {
      window.location.replace(orderValidationLinkByPaypal);
    },
  }).render('#paypal-button-container');
}

function initHostedFields() {
  // remove "amp;" from the url
  const orderValidationLinkByCard = validateOrderLinkByCard.replace(/\amp;/g, '');

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
    }).then((hf) => {
      hf.on('cardTypeChange', (event) => {
        // Change card bg depending on card type
        if (event.cards.length === 1) {
          document.querySelector('.defautl-credit-card').style.display = 'none';

          const cardImage = document.getElementById('card-image');
          cardImage.className = '';
          cardImage.classList.add(event.cards[0].type);

          document.querySelector('header').classList.add('header-slide');

          // Change the CVV length for AmericanExpress cards
          if (event.cards[0].code.size === 4) {
            hf.setAttribute({
              field: 'cvv',
              attribute: 'placeholder',
              value: 'XXXX',
            });
          }
        } else {
          document.querySelector('.defautl-credit-card').style.display = 'block';
          const cardImage = document.getElementById('card-image');
          cardImage.className = '';

          hf.setAttribute({
            field: 'cvv',
            attribute: 'placeholder',
            value: 'XXX',
          });
        }
      });

      document.querySelector('#hosted-fields-form').addEventListener('submit', (event) => {
        event.preventDefault();
        toggleLoader(true);

        // TODO : Patch a first time the order to prevent any modifications of the cart

        hf.submit({
          contingencies: ['3D_SECURE'], // only necessary if using 3D Secure verification
        }).then((payload) => {
          if (payload.liabilityShifted === undefined) { // No 3DS Contingency Passed or card not enrolled to 3ds
            window.location.replace(orderValidationLinkByCard);
            console.log('undefined');
          }

          if (payload.liabilityShifted) { // 3DS Contingency Passed - Buyer confirmed Successfully
            window.location.replace(orderValidationLinkByCard);
            console.log('success');
          }

          if (payload.liabilityShifted === false) { // 3DS Contingency Passed, but Buyer skipped 3DS
            // window.location.replace(orderValidationLinkByCard);
            console.log('error');
          }
        }).catch((err) => {
          displayCardError(err); // display alert danger with errors
          document.querySelector('#payment-confirmation button').removeAttribute('disabled'); // if errors keep the button enabled
          toggleLoader(false);
          console.log(err);
        });
      });
    });
  }
}

function displayCardError(err) {
  if (typeof err.details === 'undefined') {
    return;
  }

  const displayError = document.getElementById('hostedFieldsErrors');
  const errorList = document.getElementById('hostedFieldsErrorList');

  // reset previous messages set in the div
  errorList.innerHTML = '';

  displayError.classList.remove('hide-paypal-error');

  Object.keys(err.details).forEach((item) => {
    const errorCode = err.details[item].issue;
    const errorMessage = hostedFieldsErrors[errorCode];

    const li = document.createElement('li');
    li.appendChild(document.createTextNode(errorMessage));
    errorList.appendChild(li);
  });
}

function hideDefaultPaymentButtonIfPaypalIsChecked() {
  const conditionsToApproveId = document.getElementById('conditions-to-approve');
  const paymentDefaultButton = document.getElementById('payment-confirmation');

  const paypalOptions = document.getElementsByName('payment-option');

  for (let i = 0; i < paypalOptions.length; i++) {
    const item = paypalOptions[i];

    item.addEventListener('click', () => {
      if (item.checked && item.dataset.moduleName === paypalPaymentOption) {
        paymentDefaultButton.classList.add('paypal-hide-default');
        conditionsToApproveId.classList.add('paypal-hide-default');
      } else {
        paymentDefaultButton.classList.remove('paypal-hide-default');
        conditionsToApproveId.classList.remove('paypal-hide-default');
      }
    });
  }
}

function test(event) {
  event.preventDefault();
  document.querySelector('#hosted-fields-form button').click();
}

function toggleLoader(enable) {
  if (enable === true) {
    const span = document.createElement('span');
    span.classList.add('spinner-hosted-fields');
    document.querySelector('#payment-confirmation button').prepend(span);
  } else {
    document.querySelector('.spinner-hosted-fields').remove();
  }
}
