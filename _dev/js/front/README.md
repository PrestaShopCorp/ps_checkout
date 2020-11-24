# PS Checkout Module

**PS Checkout** is a payment solution made by Prestashop that is being adopted by more than 8.000 stores so far, and more
to come. We have developed some tools in **PS Checkout** so you can compatibilize your themes and
modules with our module easily.
 
## Integration Documentation
To integrate **PS Checkout** with your theme or module you can use any of the following solutions:

> You need to create the window.ps_checkout object by yourself. This object should exist before **PS Checkout**
> has been loaded or it will use the default configuration.

### Query Selector Replacement 
You can replace some of our query selectors used in our module.

```js
// These are the default query selectors used by PS Checkout
// If you want to override any of theese, just define it before PS Checkout gets loaded.

window.ps_checkout.selectors = {
  BASE_PAYMENT_CONFIRMATION: '#payment-confirmation [type="submit"]',

  CONDITIONS_CHECKBOXES: '#conditions-to-approve input[type="checkbox"]',

  LOADER_PARENT: 'body',

  NOTIFICATION_CONDITIONS: '.accept-cgv',
  NOTIFICATION_PAYMENT_CANCELLED: '#ps_checkout-canceled',
  NOTIFICATION_PAYMENT_ERROR: '#ps_checkout-error',
  NOTIFICATION_PAYMENT_ERROR_TEXT: '#ps_checkout-error-text',

  PAYMENT_OPTIONS: '.payment-options',

  // Only for Prestashop 1.7
  PAYMENT_OPTION_RADIOS:
    '.payment-options input[type="radio"][name="payment-option"]'
}
```

### Event Subscription
You can suscribe to some events emmited by our module.

```js
window.ps_checkout.events = new EventTarget();

// PS Checkout has been initialized but not rendered.
window.ps_checkout.events.addEventListener('init', ({details}) => {
    // Same as window.ps_checkout
    const {ps_checkout} = details;
    // ...
});

// PS Checkout Payment Option is active
window.ps_checkout.events.addEventListener('payment-option-active', ({details}) => {
    const {
        // HTMLNode of the Payment Option
        HTMLElementContainer,
        // Funding Source constant name
        fundingSource
    } = details;
    // ...
});

// PS Checkout has been rendered.
window.ps_checkout.events.addEventListener('loaded', ({details}) => {
    // Same as window.ps_checkout
    const {ps_checkout} = details;
    // ...
});
```

### Custom Rendering of Payment Tunnel

By default, PS Checkout payment tunnel and default express buttons gets rendered in your site.

You can disable that behaviour by setting the following configuration in your module
```php
Configuration::updateValue('PS_CHECKOUT_AUTO_RENDER_DISABLED', true),
```

To manually render the payment tunnel:
```js
// Once **PS Checkout** has been loaded(Read Event Subscription section)
window.ps_checkout.renderCheckout();
```

To manually render default ExpressCheckout buttons:
```js
// Once **PS Checkout** has been loaded(Read Event Subscription section)
window.ps_checkout.renderExpressCheckout();
``` 

You can manually render custom ExpressCheckout buttons for your module or theme:
```js
// Once **PS Checkout** has been loaded(Read Event Subscription section)
window.ps_checkout.renderExpressCheckout({
    HTMLElement: document.querySelector(".my-selector")
});

// If you want to associate the button to any product instead the cart you can do
window.ps_checkout.renderExpressCheckout({
    HTMLElement: document.querySelector(".my-selector"),
    productData: {
        id_product: 'Prestashop Product ID',
        id_product_attribute: 'Prestashop Product Attribute',
        id_customization: 'Prestashop Product Customization',
        quantity_wanted: 'Prestashop Product Quantity',
    }
});
``` 

> Even if the `window.ps_checkout.renderExpressCheckout` is available in Prestashop 1.6, express checkout
> buttons will only work for Prestashop 1.7 at this given moment. This will be available in
> Prestashop 1.6 soon.
