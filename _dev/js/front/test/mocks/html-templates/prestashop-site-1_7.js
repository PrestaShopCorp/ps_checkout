import PRODUCT_DATASET from '../data/product-dataset.json';

export function cleanSite() {
  document.body.removeAttribute('id');
  document.body.innerHTML = '';
}

export function mockCartPage() {
  document.body.id = 'cart';
}

export function mockCheckoutPage() {
  document.body.id = 'checkout';
}

export function mockCheckoutPaymentStepPage() {
  document.body.id = 'checkout';
  document.body.innerHTML = `<div data-module-name="ps_checkout-paypal" />`;
}

export function mockCheckoutPersonalInformationStepPage() {
  document.body.id = 'checkout';
  document.body.innerHTML = `<div id="checkout-personal-information-step" class="-current" />`;
}

export function mockProductPage() {
  document.body.id = 'product';
  document.body.innerHTML = `
    <div id="product-details" />
  `;

  document
    .getElementById('product-details')
    .setAttribute('data-product', JSON.stringify(PRODUCT_DATASET));
}

export function mockCheckoutVars() {
  window.ps_checkoutPayPalOrderId = '';
  window.ps_checkoutPayPalClientToken = '';
  window.ps_checkoutPayPalSdkConfig = {
    clientId: 'test',
    currency: 'EUR',
    intent: 'capture',
    integrationDate: '2022-14-06',
    components: 'marks,funding-eligibility,buttons'
  };
}
