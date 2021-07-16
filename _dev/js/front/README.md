# Ps Checkout Module

**Ps Checkout** is a Paypal payment solution integrated by **Prestashop** that is being used by thousands of stores 
so far. It was designed to work with the default Prestashop Theme, but it contains a set of settings that allows 
you to customize it for your own Prestashop themes and modules.

## In-depth module description
These schemas provide further info on how Ps Checkout generates the HTML on the payment tunnel and how we set the
initialization javascript code.

### HTML Generation
![HTML Generation Schema](docs/html-generation.jpg)

### Front-end Initialization
![Front-end Initialization Schema](docs/front-end-initialization.jpg)

#### Notes
- Only eligible payment options will unhide at the end of the render process, even if they are active on the
configuration.
- You can check which payment options will be tested for eligibility by checking the variable
`ps_checkoutFundingSourcesSorted` in your devtools.
- The integrated card form (hosted fields) appears if all these conditions are met:
    - The card payment option is active
    - The 'Integrated Credit Card Fields' option is active in the module configuration (Advanced Options)*
    - The card payment option is eligible (checked by PayPal)
    - The PayPal SDK has been loaded with a client token**

\* You can verify this by checking the variable `ps_checkoutHostedFieldsEnabled` in your devtools.

\** You can verify this by looking the 'Network' tab in your devtools and checking that the endpoint
`/module/ps_checkout/token` has been called without errors.
 
## Customizing Ps Checkout

**Ps Checkout** internally uses a global ``window.ps_checkout`` javascript object to implement **Paypal Checkout**
specifications. You can use that object wherever you need to render the payment plugin. You can also customize 
the way the payment tunnel is rendered by defining your own html selectors, and you can subscribe to a few events 
to interact with Ps Checkout.

### Query Selector Replacement

**Ps Checkout** uses query selectors to identify each part of the payment plugin in your html. If you want to use your 
own selectors instead, you can define a ```window.ps_checkout.selectors``` object before loading **Ps Checkout** module, 
so that configuration will be merged with the default one.

```js
// Default query selectors used by PS Checkout
window.ps_checkout.selectors = {
  // The default payment confirmation button created by Prestashop. This will be used on the payment tunnel
  // with a express checkout order or as confirmation button for the integrated card form (hosted fields) in 1.7
  BASE_PAYMENT_CONFIRMATION: '#payment-confirmation [type="submit"]',

  // All the TOS-like checkboxes that must be checked before the client can confirm the order.
  CONDITIONS_CHECKBOXES: '#conditions-to-approve input[type="checkbox"]',

  // Container where the 'loading screen' will be appended. The loader screen will appear to lock the
  // site until the payment has been confirmed/cancelled/errored.
  LOADER_PARENT: 'body',

  // Notification promted when the user has not accepted all the checkboxes defined with
  // `CONDITIONS_CHECKBOXES`
  NOTIFICATION_CONDITIONS: '.accept-cgv',

  // Notification promted on payment cancel
  NOTIFICATION_PAYMENT_CANCELLED: '#ps_checkout-canceled',
  // Notification promted on payment error
  NOTIFICATION_PAYMENT_ERROR: '#ps_checkout-error',
  // Container for the error extra information
  NOTIFICATION_PAYMENT_ERROR_TEXT: '#ps_checkout-error-text',

  // Container for all the payment options
  PAYMENT_OPTIONS: '.payment-options',

  // Only for Prestashop 1.7
  // Radio buttons of each payment option. This will be used to add a listener that hides the notifications
  // on payment option selection change.
  PAYMENT_OPTION_RADIOS:
    '.payment-options input[type="radio"][name="payment-option"]',
  
  // Container where the ExpressCheckout button will be appended.
  EXPRESS_CHECKOUT_CONTAINER_PRODUCT_PAGE:
    '#product .product-add-to-cart .product-quantity',
  EXPRESS_CHECKOUT_CONTAINER_CART_PAGE:
    '#cart .cart-summary .cart-detailed-actions',
  EXPRESS_CHECKOUT_CONTAINER_CHECKOUT_PAGE:
    '#checkout-personal-information-step .content',
}
```

#### Use Cases
##### I want to use my own notifications
```js
// In a .js file loaded before Ps Checkout main file (front.js)
window.ps_checkout = {
  selectors: {
    NOTIFICATION_CONDITIONS: '.my-class-for-notifications.tos-not-accepted',
  
    NOTIFICATION_PAYMENT_CANCELLED: '.my-class-for-notifications.on-payment-cancelled',
    NOTIFICATION_PAYMENT_ERROR: '.my-class-for-notifications.on-payment-error',
    NOTIFICATION_PAYMENT_ERROR_TEXT: '.my-class-for-notifications.on-payment-error .text-body',
  }
}
```

### Event Subscription
**Ps Checkout** emits some events to which you can subscribe, you just need to create a new EventTarget inside 
window.ps_checkout.events:  

```js
window.ps_checkout.events = new EventTarget();
// PS Checkout has been initialized but not rendered.
window.ps_checkout.events.addEventListener('init', ({detail}) => {
    // === window.ps_checkout
    const {ps_checkout} = detail;
    // ...
});

// Payment Option is active
window.ps_checkout.events.addEventListener('payment-option-active', ({detail}) => {
    const {
        // HTMLNode of the Payment Option
        HTMLElementContainer,
        // Funding Source constant name
        fundingSource
    } = detail;
    // ...
});

// PS Checkout has just been rendered.
window.ps_checkout.events.addEventListener('loaded', ({detail}) => {
    // === window.ps_checkout
    const {ps_checkout} = detail;
    // ...
});
```

#### Use Cases
##### (1.7) I want to wrap a container arround my payment options but Ps Checkout just hide the inner container
```js
// In a .js file loaded before Ps Checkout main file (front.js)
window.ps_checkout = {
  events: new EventTarget(),
}

window.ps_checkout.events.addEventListener('init', () => {
    const paymentOptions = Array.prototype.slice.call(
        document.querySelectorAll('[data-module-name^="ps_checkout"]')
    );

    const paymentOptionContainers = paymentOptions
        .map(paymentOption => paymentOption
            // Default payment option container
            .parentElement.parentElement
            // Container added by my module / theme
            .parentElement);

    // Hide all wrappers
    paymentOptionContainers.forEach(myContainer => myContainer.style.display = 'none');
});

window.ps_checkout.events.addEventListener('payment-option-active', ({detail}) => {
    const { HTMLElementContainer } = detail;
    const myHTMLElementContainer = HTMLElementContainer.parentElement;

    myHTMLElementContainer.style.display = '';
});
```

### Styling ExpressCheckout PayPal Button
If you want to use your
own style, you can define a `window.ps_checkout.PayPalExpressCheckoutButtonCustomization` object before loading **PrestaShop Checkout** module,
so that configuration will be merged with the default one.

```js
// In a .js file loaded before PrestaShop Checkout main file (front.js)
window.ps_checkout.PayPalExpressCheckoutButtonCustomization = {
  layout: 'vertical',
  color: 'blue',
  shape: 'pill',
  label: 'checkout'
};
```

### Manual Rendering of Payment Tunnel
By default, **PS Checkout** will render the payment tunnel, and the default express checkout buttons 
(if you have activated Express Checkout), wherever needed in the default theme.

You can disable this behaviour by setting the following php configuration on your module:
```php
Configuration::updateValue('PS_CHECKOUT_AUTO_RENDER_DISABLED', true),
```

Now, you can manually render:

* The payment tunnel:
```js
// Once **PS Checkout** has been loaded
window.ps_checkout.renderCheckout();
```

* The default ExpressCheckout buttons:
```js
// Once **PS Checkout** has been loaded
window.ps_checkout.renderExpressCheckout();
``` 

* Your own Express Checkout buttons:
```js
// Once **PS Checkout** has been loaded
window.ps_checkout.renderExpressCheckout({
    HTMLElement: document.querySelector(".my-selector")
});

// To associate the button to a custom product instead of the default cart 
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

#### Use Cases
##### I want PsCheckout to wait for my module to do something before rendering the payment tunnel
```js
// With the default rendering disabled
// In a .js file loaded before Ps Checkout main file (front.js)
window.ps_checkout = {
  events: new EventTarget(),
}

window.ps_checkout.events.addEventListener('loaded', () => {
    // ... Your initialization code
    window.ps_checkout.renderCheckout();
});
```
> The 'payment-option-active' event won't be triggered until `window.ps_checkout.renderCheckout();` has been executed. 


##### I want Ps Checkout to wait for my module to do something before rendering the default express checkout buttons
```js
// With the default rendering disabled
// In a .js file loaded before Ps Checkout main file (front.js)
window.ps_checkout = {
  events: new EventTarget(),
}

window.ps_checkout.events.addEventListener('loaded', () => {
    // ... Your initialization code
    window.ps_checkout.renderExpressCheckout();
});
```

##### I want to render my own Express Checkout buttons
```js
// With the default rendering disabled
// In a .js file loaded before Ps Checkout main file (front.js)
window.ps_checkout = {
  events: new EventTarget(),
}

window.ps_checkout.events.addEventListener('loaded', () => {
    window.ps_checkout.renderExpressCheckout({
        HTMLElement: document.querySelector(".my-express-checkout-button.first-button")
    });

    window.ps_checkout.renderExpressCheckout({
        HTMLElement: document.querySelector(".my-express-checkout-button.second-button")
    });

    window.ps_checkout.renderExpressCheckout({
        HTMLElement: document.querySelector(".my-express-checkout-button.third-button")
    });
});
```

## WIP
> Even if `window.ps_checkout.renderExpressCheckout` is available in Prestashop 1.6, Express Checkout
> buttons will only be rendered for Prestashop 1.7 for the moment. This will be available in Prestashop 1.6 soon.

## TODO
- Should we also allow the contributors to subscribe to SDK events (onCancel, onCapture...) ?
