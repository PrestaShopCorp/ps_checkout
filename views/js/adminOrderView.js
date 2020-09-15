'use strict';
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

let ps_checkout = {};
const {$} = window;

(function() {
  let orderRequestFetchDone = function(orderPayPalContainer, orderPayPalLoaderContainer, payPalOrderNotification, data) {
    if (undefined !== data.content) {
      $(orderPayPalContainer).append(data.content);
    }

    if (undefined !== data.errors) {
      for (const error of data.errors) {
        $(orderPayPalContainer).append(payPalOrderNotification.createErrorHTMLElement({
          text: error,
          class: 'danger',
        }));
      }
    }

    $(orderPayPalLoaderContainer).hide();
  }

  let orderRequestDone = function(modal, modalNotificationContainer, modalSubmitButton, modalLoaderContainer, payPalOrderNotification, config, data) {
    if (undefined !== data.content) {
      let payPalOrderFetcher = new PayPalOrderFetcher(config);

      modal.on('hidden.bs.modal', function () {
        $(config.orderPayPalNotificationsContainer).append(payPalOrderNotification.createErrorHTMLElement({
          text: data.content,
          class: 'success',
        }));

        $(config.orderPayPalContainer).empty();
        $(config.orderPayPalLoaderContainer).show();

        payPalOrderFetcher.execute();
      });

      modal.modal('hide');
    }

    if (undefined !== data.errors) {
      for (const error of data.errors) {
        $(modalNotificationContainer).append(payPalOrderNotification.createErrorHTMLElement({
          text: error,
          class: 'danger',
        }));

        modalSubmitButton.prop('disabled', false);
      }
    }

    $(modalLoaderContainer).hide();
  }

  let orderRequestFail = function(orderPayPalNotificationsContainer, orderPayPalLoaderContainer, payPalOrderNotification, jqXHR, errorThrown) {
    $(orderPayPalNotificationsContainer).append(payPalOrderNotification.createErrorHTMLElement({
      text: errorThrown,
      class: 'danger',
    }));

    if (undefined !== jqXHR.responseJSON && undefined !== jqXHR.responseJSON.content) {
      $(orderPayPalNotificationsContainer).append(payPalOrderNotification.createErrorHTMLElement({
        text: jqXHR.responseJSON.content,
        class: 'danger',
      }));
    }

    $(orderPayPalLoaderContainer).hide();
  }

  let submitForm = function(event, config, element) {
    event.preventDefault();
    const modal = element.parents(config.orderPayPalModalContainer);
    const modalNotificationContainer = modal.find(config.orderPayPalModalNotificationsContainer);
    const modalLoaderContainer = modal.find(config.orderPayPalModalLoaderContainer);
    const modalSubmitButton = element.find('button[type="submit"]');
    const payPalOrderNotification = new PayPalOrderNotification();

    $(modalLoaderContainer).show();
    modalSubmitButton.prop('disabled', true);

    let payPalRequest = $.ajax({
      type: 'POST',
      headers: {"cache-control": "no-cache"},
      cache: false,
      dataType: 'json',
      url: `${config.orderPayPalBaseUrl}&rand=${new Date().getTime()}`,
      data: element.serialize(),
    });

    payPalRequest.done(function(data) {
      orderRequestDone(modal, modalNotificationContainer, modalSubmitButton, modalLoaderContainer, payPalOrderNotification, config, data);
    });

    payPalRequest.fail(function(jqXHR, textStatus, errorThrown) {
      orderRequestFail(modalNotificationContainer, modalLoaderContainer, payPalOrderNotification, jqXHR, errorThrown);
    });
  }

  /**
   * @param {object} config
   * @param {boolean} config.legacy - Use legacy style
   * @param {int} config.orderPrestaShopId - PrestaShop Order identifier
   * @param {string} config.orderPayPalBaseUrl - Base url used for request
   * @param {string} config.orderPayPalContainer - HTML element identifier
   * @param {string} config.orderPayPalLoaderContainer - HTML element identifier
   * @param {string} config.orderPayPalNotificationsContainer - HTML element identifier
   * @constructor
   */
  let PayPalOrderFetcher = function(config) {
    this.execute = function() {
      let payPalOrderNotification = new PayPalOrderNotification();
      let payPalOrderRequest = $.ajax({
        type: 'POST',
        headers: {"cache-control": "no-cache"},
        cache: false,
        dataType: 'json',
        url: `${config.orderPayPalBaseUrl}&rand=${new Date().getTime()}`,
        data: {
          ajax: 1,
          legacy: config.legacy,
          action: 'FetchOrder',
          id_order : config.orderPrestaShopId
        },
      });

      payPalOrderRequest.done(function(data) {
        orderRequestFetchDone(config.orderPayPalContainer, config.orderPayPalLoaderContainer, payPalOrderNotification, data);
      });

      payPalOrderRequest.fail(function(jqXHR, textStatus, errorThrown) {
        orderRequestFail(config.orderPayPalNotificationsContainer, config.orderPayPalLoaderContainer, payPalOrderNotification, jqXHR, errorThrown);
      });
    };
  };

  /**
   * @param {object} config
   * @param {boolean} config.legacy - Use legacy style
   * @param {int} config.orderPrestaShopId - PrestaShop Order identifier
   * @param {string} config.orderPayPalBaseUrl - Base url used for request
   * @param {string} config.orderPayPalContainer - HTML element identifier
   * @param {string} config.orderPayPalLoaderContainer - HTML element identifier
   * @param {string} config.orderPayPalNotificationsContainer - HTML element identifier
   * @param {string} config.orderPayPalRefundButton - HTML element identifier
   * @param {string} config.orderPayPalModalContainerPrefix - HTML element identifier
   * @param {string} config.orderPayPalModalContainer - HTML element identifier
   * @param {string} config.orderPayPalModalNotificationsContainer - HTML element identifier
   * @param {string} config.orderPayPalModalContentContainer - HTML element identifier
   * @param {string} config.orderPayPalModalLoaderContainer - HTML element identifier
   * @param {string} config.orderPayPalModalRefundForm - HTML element identifier
   * @constructor
   */
  let PayPalOrderRefund = function(config) {
    this.initialize = function() {
      // wait for dom ready
      $(function() {
        $(document).on('click', config.orderPayPalRefundButton, function () {
          const refundModal = $(config.orderPayPalModalContainerPrefix + $(this).attr('data-transaction-id'));
          $(config.orderPayPalNotificationsContainer).empty();
          refundModal.find(config.orderPayPalModalLoaderContainer).hide();
          refundModal.modal('show');
        });

        $(document).on('change', 'input[name="orderPayPalRefundAmount"]', function () {
          const refundModal = $(this).parents(config.orderPayPalModalContainer);
          refundModal.find(config.orderPayPalModalNotificationsContainer).empty();
        });

        $(document).on('submit', config.orderPayPalModalRefundForm, function (event) {
          submitForm(event, config, $(this));
        });
      });
    };
  };

  /**
   * @param {object} config
   * @param {boolean} config.legacy - Use legacy style
   * @param {int} config.orderPrestaShopId - PrestaShop Order identifier
   * @param {string} config.orderPayPalBaseUrl - Base url used for request
   * @param {string} config.orderPayPalContainer - HTML element identifier
   * @param {string} config.orderPayPalLoaderContainer - HTML element identifier
   * @param {string} config.orderPayPalNotificationsContainer - HTML element identifier
   * @param {string} config.orderPayPalRefundButton - HTML element identifier
   * @param {string} config.orderPayPalCaptureButton - HTML element identifier
   * @param {string} config.orderPayPalModalContainerPrefix - HTML element identifier
   * @param {string} config.orderPayPalModalCaptureContainerPrefix - HTML element identifier
   * @param {string} config.orderPayPalModalContainer - HTML element identifier
   * @param {string} config.orderPayPalModalNotificationsContainer - HTML element identifier
   * @param {string} config.orderPayPalModalContentContainer - HTML element identifier
   * @param {string} config.orderPayPalModalLoaderContainer - HTML element identifier
   * @param {string} config.orderPayPalModalRefundForm - HTML element identifier
   * @param {string} config.orderPayPalModalCaptureForm - HTML element identifier
   * @constructor
   */
  let PayPalOrderCapture = function(config) {
    this.initialize = function() {
      // wait for dom ready
      $(function() {
        $(document).on('click', config.orderPayPalCaptureButton, function () {
          const captureModal = $(config.orderPayPalModalCaptureContainerPrefix + $(this).attr('data-transaction-id'));
          $(config.orderPayPalNotificationsContainer).empty();
          captureModal.find(config.orderPayPalModalLoaderContainer).hide();
          captureModal.modal('show');
        });

        $(document).on('submit', config.orderPayPalModalCaptureForm, function (event) {
          submitForm(event, config, $(this));
        });
      });
    };
  };

  /**
   * @param {object} config
   * @param {boolean} config.legacy - Use legacy style
   * @param {int} config.orderPrestaShopId - PrestaShop Order identifier
   * @param {string} config.orderPayPalBaseUrl - Base url used for request
   * @param {string} config.orderPayPalContainer - HTML element identifier
   * @param {string} config.orderPayPalLoaderContainer - HTML element identifier
   * @param {string} config.orderPayPalNotificationsContainer - HTML element identifier
   * @param {string} config.orderPayPalRefundButton - HTML element identifier
   * @param {string} config.orderPayPalModalContainerPrefix - HTML element identifier
   * @param {string} config.orderPayPalModalVoidContainerPrefix - HTML element identifier
   * @param {string} config.orderPayPalModalContainer - HTML element identifier
   * @param {string} config.orderPayPalModalNotificationsContainer - HTML element identifier
   * @param {string} config.orderPayPalModalContentContainer - HTML element identifier
   * @param {string} config.orderPayPalModalLoaderContainer - HTML element identifier
   * @param {string} config.orderPayPalModalRefundForm - HTML element identifier
   * @param {string} config.orderPayPalModalVoidForm - HTML element identifier
   * @constructor
   */
  let PayPalOrderVoid = function(config) {
    this.initialize = function() {
      // wait for dom ready
      $(function() {
        $(document).on('click', config.orderPayPalVoidButton, function () {
          const voidModal = $(config.orderPayPalModalVoidContainerPrefix + $(this).attr('data-transaction-id'));
          $(config.orderPayPalNotificationsContainer).empty();
          voidModal.find(config.orderPayPalModalLoaderContainer).hide();
          voidModal.modal('show');
        });

        $(document).on('submit', config.orderPayPalModalVoidForm, function (event) {
          submitForm(event, config, $(this));
        });
      });
    };
  };

  /**
   * @constructor
   */
  let PayPalOrderNotification = function() {
    /**
     *
     * @param {object} params
     * @param {string} params.text - Error text
     * @param {string} params.class - Error class
     *
     * @returns {HTMLElement}
     */
    this.createErrorHTMLElement = function (params) {
      let errorContentContainer = document.createElement('div');
      errorContentContainer.className = 'alert-text';
      let errorContent = document.createTextNode(params.text);
      errorContentContainer.appendChild(errorContent);
      let errorContainer = document.createElement('div');
      errorContainer.className = 'd-print-none hidden-print alert alert-' + params.class;
      errorContainer.appendChild(errorContentContainer);

      return errorContainer;
    }
  };

  /**
   * Initialize ps_checkout
   *
   * @param {object} config
   * @param {boolean} config.legacy - Use legacy style
   * @param {int} config.orderPrestaShopId - PrestaShop Order identifier
   * @param {string} config.orderPayPalBaseUrl - Base url used for request
   * @param {string} config.orderPayPalContainer - HTML element identifier
   * @param {string} config.orderPayPalLoaderContainer - HTML element identifier
   * @param {string} config.orderPayPalNotificationsContainer - HTML element identifier
   * @param {string} config.orderPayPalRefundButton - HTML element identifier
   * @param {string} config.orderPayPalModalContainerPrefix - HTML element identifier
   * @param {string} config.orderPayPalModalContainer - HTML element identifier
   * @param {string} config.orderPayPalModalNotificationsContainer - HTML element identifier
   * @param {string} config.orderPayPalModalContentContainer - HTML element identifier
   * @param {string} config.orderPayPalModalLoaderContainer - HTML element identifier
   * @param {string} config.orderPayPalModalRefundForm - HTML element identifier
   */
  ps_checkout.initialize = function(config) {
    let payPalOrderFetcher = new PayPalOrderFetcher(config);
    payPalOrderFetcher.execute();

    let payPalOrderRefund = new PayPalOrderRefund(config);
    payPalOrderRefund.initialize();

    let payPalOrderCapture = new PayPalOrderCapture(config);
    payPalOrderCapture.initialize();

    let payPalOrderVoid = new PayPalOrderVoid(config);
    payPalOrderVoid.initialize();
  };
})();
