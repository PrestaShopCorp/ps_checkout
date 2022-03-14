import Bottle from 'bottlejs';

import { PsCheckoutApi } from '../api/ps-checkout.api';
import { PayPalSdkConfig } from '../config/paypal-sdk.config';
import { PsCheckoutConfig } from '../config/ps-checkout.config';
import { PayPalSdkComponent } from '../components/common/paypal-sdk.component';
import { PsCheckoutComponent } from '../components/ps-checkout.component';
import { PsCheckoutExpressComponent } from '../components/ps-checkout-express.component';
import { PsCheckoutExpressPayLaterComponent } from '../components/ps-checkout-express-paylater.component';
import { HTMLElementService } from '../service/html-element.service';
import { PayPalService } from '../service/paypal.service';
import { PrestashopService } from '../service/prestashop.service';
import { PsCheckoutService } from '../service/ps-checkout.service';
import { TranslationService } from '../service/translation.service';
import { QuerySelectorService } from '../service/query-selector.service';
import { PaymentOptionsLoaderComponent } from '../components/common/payment-options-loader.component';
import { PayLaterOffersComponent } from '../components/common/pay-later-offers.component';

function initService(app) {
  return service => () => new service(app);
}

/**
 * @param {ContainerAwareClass} app
 */
function initContainer(app) {
  const bottle = app.bottle;
  const serviceFactory = initService(app);

  bottle.value('PayPalSdkConfig', PayPalSdkConfig);
  bottle.value('PsCheckoutConfig', PsCheckoutConfig);

  bottle.service('PrestashopService', PrestashopService);

  bottle.factory('HTMLElementService', serviceFactory(HTMLElementService));
  bottle.factory('QuerySelectorService', serviceFactory(QuerySelectorService));
  bottle.factory('PsCheckoutApi', serviceFactory(PsCheckoutApi));
  bottle.factory('PsCheckoutService', serviceFactory(PsCheckoutService));
  bottle.factory('TranslationService', serviceFactory(TranslationService));
  bottle.factory(
    'PaymentOptionsLoaderComponent',
    serviceFactory(PaymentOptionsLoaderComponent)
  );

  bottle.factory('$', container => {
    return id => container.TranslationService.getTranslationString(id);
  });
}

export class App {
  constructor() {
    this.bottle = new Bottle();
    this.container = this.bottle.container;
    initContainer(this);

    this.psCheckoutConfig = this.container.PsCheckoutConfig;

    this.prestashopService = this.container.PrestashopService;
    this.psCheckoutService = this.container.PsCheckoutService;
    this.paymentOptionsLoader = this.container.PaymentOptionsLoaderComponent;

    this.$ = this.container.$;

    this.root = null;
  }

  exposeAPI() {
    window.ps_checkout.renderCheckout = () => {
      return this.renderCheckout();
    };

    window.ps_checkout.renderExpressCheckout = props => {
      return this.renderExpressCheckout(props);
    };

    window.ps_checkout.renderPayLaterOfferMessage = props => {
      return this.renderPayLaterOfferMessage(props);
    };
  }

  async initPayPalService(useToken = false) {
    if (!this.container.PayPalSDK) {
      const token = useToken
        ? await this.psCheckoutService.getPayPalToken()
        : '';

      try {
        const sdk = await new PayPalSdkComponent(this, {
          token
        }).render().promise;

        this.bottle.value('PayPalSDK', sdk);
        this.bottle.factory('PayPalService', initService(this)(PayPalService));
      } catch (e) {
        throw new Error(this.$('error.paypal-sdk'));
      }
    }
  }

  async renderCheckout() {
    await this.initPayPalService(this.psCheckoutConfig.hostedFieldsEnabled);
    new PsCheckoutComponent(this).render();
  }

  async renderExpressCheckout(props) {
    await this.initPayPalService();
    new PsCheckoutExpressComponent(this, props).render();
  }

  async renderExpressCheckoutPayLater(props) {
    await this.initPayPalService();

    if (this.container.PayPalSDK.Marks({ fundingSource: 'paylater' }).isEligible()) {
      new PsCheckoutExpressPayLaterComponent(this, props).render();
    }
  }

  async renderPayLaterOfferMessage(props) {
    await this.initPayPalService();
    new PayLaterOffersComponent(this, props).render();
  }

  async render() {
    this.exposeAPI();

    if (!this.psCheckoutConfig.autoRenderDisabled) {
      console.log('ps_checkout autoRenderDisabled');

      if (document.body.id === 'product') {
        console.log('ps_checkout product page call renderPayLaterOfferMessage');
        await this.renderPayLaterOfferMessage({
          querySelector: '.product-prices'
        });
      }

      if (document.body.id === 'index') {
        await this.renderPayLaterOfferMessage({
          querySelector: '.page-home'
        });
      }

      if (document.body.id === 'category') {
        await this.renderPayLaterOfferMessage({
          querySelector: '.page-home'
        });
      }

      if (document.body.id === 'cart') {
        await this.renderPayLaterOfferMessage({
          querySelector: '.cart-grid-right'
        });
      }

      if (document.body.id === 'checkout') {
        await this.renderPayLaterOfferMessage({
          querySelector: '.cart-grid-right'
        });
      }

      if (
        this.prestashopService.isCartPage() ||
        this.prestashopService.isOrderPersonalInformationStepPage() ||
        this.prestashopService.isProductPage()
      ) {
        await this.renderExpressCheckout();
        await this.renderExpressCheckoutPayLater();

        if (this.prestashopService.isOrderPersonalInformationStepPage()) {
          await this.renderCheckout();
        }

        return this;
      }

      if (this.prestashopService.isOrderPaymentStepPage()) {
        await this.renderCheckout();
        return this;
      } else if (this.prestashopService.isOrderPage()) {
        this.paymentOptionsLoader.hide();
        return this;
      }
    }

    return this;
  }
}
