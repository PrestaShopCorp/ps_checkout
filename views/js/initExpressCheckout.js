/**
 * 2007-2019 PrestaShop and Contributors
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
 * @copyright 2007-2019 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 * International Registered Trademark & Property of PrestaShop SA
 */

/**
 * Waiting that the dom is loaded
 */
document.addEventListener('DOMContentLoaded', function () {
  // if express checkout is used on cart page (expressCheckout hook)
  if (displayMode === 'cart') {
    window.prestashop.on('updatedCart', function () {
      initPaypalSmartButtons();
    });
  }

  if (displayMode === 'product') {
    const addToCartButton = document.getElementsByClassName('product-add-to-cart');
    addToCartButton[0].parentNode.insertBefore(document.getElementById('pscheckout-express-checkout'), addToCartButton[0].nextSibling);
  }

  if (displayMode === 'checkout' && !isPs176) {
    const personalInformationTop = document.querySelector('#checkout-personal-information-step .content')
    personalInformationTop.insertBefore(document.getElementById('pscheckout-express-checkout'), personalInformationTop.firstChild);
  }

  // wait paypal sdk to be fully loaded
  const interval = setInterval(function () {
    if (window.paypalSdkPsCheckoutEC !== undefined) {
      initPaypalSmartButtons();
      clearInterval(interval);
    }
  }, 200);
});

/**
 * Init paypal buttons with the given paypal order
 *
 * @param {int} paypalOrder
 */
function initPaypalSmartButtons() {
  const controllerLink = expressCheckoutController.replace(/\amp;/g, '');

  window.paypalSdkPsCheckoutEC.Buttons({
    style: {
      size: 'responsive',
      shape: 'pill',
      label: 'pay',
    },
    onRender() {
      cleanAllExpressCheckoutInstance();
      showExpressCheckout();
    },
    createOrder(data, actions) {
      return createPaypalOrder().then((result) => {
        if (result.status === false) {
          return;
        }

        if (displayMode === 'product') {
          prestashop.emit('updateCart', {
            reason: 'update',
          });
        }

        const paypalScript = document.getElementById('psCheckoutPaypalSdk');
        paypalScript.setAttribute('data-client-token', result.body.client_token);

        return result.body.id;
      }).catch(() => {
        throw new Error('Not able to create a paypal order');
      });
    },
    onApprove(paypal, actions) {
      actions.order.get().then((order) => {
        redirect(order, controllerLink);
      });
    },
    onError(err) {
      console.log(err);
    }
  }).render('#paypal-button-container');
}

/**
 * Ajax request to create and retrieve paypal order
 */
async function createPaypalOrder() {
  const controllerLink = expressCheckoutController.replace(/\amp;/g, '');

  return new Promise(function (resolve, reject) {
    // construct form data
    const form = new FormData();
    form.append('ajax', true);
    form.append('action', 'CreatePaypalOrder');

    if (displayMode === 'product') {
      const productDetails = JSON.parse(document.getElementById('product-details').dataset.product);
      const product = {
        'id_product': productDetails.id_product,
        'id_product_attribute': productDetails.id_product_attribute,
        'id_customization': productDetails.id_customization,
        'quantity_wanted': productDetails.quantity_wanted,
      }

      form.append('product', JSON.stringify(product));
    }

    fetch(controllerLink, {
      body: form,
      method: 'post',
    }).then((response) => {
      resolve(response.json());
    }).catch((err) => {
      reject(err);
    });
  });
}

/**
 * Toggle express checkout visibility
 */
function showExpressCheckout() {
  const expressCheckout = document.getElementById('pscheckout-express-checkout');
  expressCheckout.style.display = 'block';
}


/**
 * Check if an expressCheckout instance is already instanced
 */
function expressCheckoutAlreadyExist() {
  const expressCheckout = document.getElementById('paypal-button-container');
  const instances = expressCheckout.childNodes;

  if (instances.length === 0) {
    return false;
  }

  return true;
}

/**
 * Clean all existing express checkout
 */
function cleanAllExpressCheckoutInstance() {
  const expressCheckout = document.getElementById('paypal-button-container');
  const instances = expressCheckout.childNodes;

  instances.forEach((instance) => {
    instance.remove();
  });
}

/**
 * Redirect with post data
 *
 * @param {object} paypalOrder
 * @param {string} url
 */
function redirect(paypalOrder, url) {
  // Create an hidden form
  const form = document.createElement('form');
  form.action = url;
  form.method = 'POST';
  form.style.display = 'none';

  // Create an input with the paypal order as value
  const paypalOrderInput = document.createElement('input');
  paypalOrderInput.value = JSON.stringify(paypalOrder);
  paypalOrderInput.name = 'paypalOrder';

  // Create an input with a token
  const expressCheckoutTokenInput = document.createElement('input');
  expressCheckoutTokenInput.value = prestashop.token;
  expressCheckoutTokenInput.name = 'expressCheckoutToken';

  // Put inputs in the form
  form.appendChild(paypalOrderInput);
  form.appendChild(expressCheckoutTokenInput);
  // Append the form the body
  document.body.appendChild(form);

  // submit the form
  form.submit();
}
