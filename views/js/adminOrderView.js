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
  let PayPalOrderFetcher = function(config) {
    this.execute = function() {
      let payPalOrderNotification = new PayPalOrderNotification(config);
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
        if (undefined !== data.content) {
          $(config.orderPayPalContainer).append(data.content);
        }

        if (undefined !== data.errors) {
          for (const error of data.errors) {
            $(config.orderPayPalContainer).append(payPalOrderNotification.createErrorHTMLElement({
              text: error,
              class: 'danger',
            }));
          }
        }

        $(config.orderPayPalLoaderContainer).hide();
      });

      payPalOrderRequest.fail(function(jqXHR, textStatus, errorThrown) {
        $(config.orderPayPalNotificationsContainer).append(payPalOrderNotification.createErrorHTMLElement({
          text: errorThrown,
          class: 'danger',
        }));

        if (undefined !== jqXHR.responseJSON && undefined !== jqXHR.responseJSON.content) {
          $(config.orderPayPalNotificationsContainer).append(payPalOrderNotification.createErrorHTMLElement({
            text: jqXHR.responseJSON.content,
            class: 'danger',
          }));
        }

        $(config.orderPayPalLoaderContainer).hide();
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
          event.preventDefault();
          const refundModal = $(this).parents(config.orderPayPalModalContainer);
          const refundModalNotificationContainer = refundModal.find(config.orderPayPalModalNotificationsContainer);
          const refundModalLoaderContainer = refundModal.find(config.orderPayPalModalLoaderContainer);
          const refundModalSubmitButton = $(this).find('button[type="submit"]');
          const payPalOrderNotification = new PayPalOrderNotification(config);

          $(refundModalLoaderContainer).show();
          refundModalSubmitButton.prop('disabled', true);

          let payPalRefundRequest = $.ajax({
            type: 'POST',
            headers: {"cache-control": "no-cache"},
            cache: false,
            dataType: 'json',
            url: `${config.orderPayPalBaseUrl}&rand=${new Date().getTime()}`,
            data: $(this).serialize(),
          });

          payPalRefundRequest.done(function(data) {
            if (undefined !== data.content) {
              let payPalOrderFetcher = new PayPalOrderFetcher(config);

              refundModal.on('hidden.bs.modal', function () {
                $(config.orderPayPalNotificationsContainer).append(payPalOrderNotification.createErrorHTMLElement({
                  text: data.content,
                  class: 'success',
                }));

                $(config.orderPayPalContainer).empty();
                $(config.orderPayPalLoaderContainer).show();

                payPalOrderFetcher.execute();
              });

              refundModal.modal('hide');
            }

            if (undefined !== data.errors) {
              for (const error of data.errors) {
                $(refundModalNotificationContainer).append(payPalOrderNotification.createErrorHTMLElement({
                  text: error,
                  class: 'danger',
                }));

                refundModalSubmitButton.prop('disabled', false);
              }
            }

            $(refundModalLoaderContainer).hide();
          });

          payPalRefundRequest.fail(function(jqXHR, textStatus, errorThrown) {
            $(refundModalNotificationContainer).append(payPalOrderNotification.createErrorHTMLElement({
              text: errorThrown,
              class: 'danger',
            }));

            if (undefined !== jqXHR.responseJSON && undefined !== jqXHR.responseJSON.content) {
              $(refundModalNotificationContainer).append(payPalOrderNotification.createErrorHTMLElement({
                text: jqXHR.responseJSON.content,
                class: 'danger',
              }));
            }

            $(refundModalLoaderContainer).hide();
          });
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
   * @param {string} config.orderPayPalModalCaptureContainer - HTML element identifier
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
          event.preventDefault();
          const captureModal = $(this).parents(config.orderPayPalModalCaptureContainer);
          const captureModalNotificationContainer = captureModal.find(config.orderPayPalModalNotificationsContainer);
          const captureModalLoaderContainer = captureModal.find(config.orderPayPalModalLoaderContainer);
          const captureModalSubmitButton = $(this).find('button[type="submit"]');
          const payPalOrderNotification = new PayPalOrderNotification(config);

          $(captureModalLoaderContainer).show();
          captureModalSubmitButton.prop('disabled', true);

          let payPalCapturRequest = $.ajax({
            type: 'POST',
            headers: {"cache-control": "no-cache"},
            cache: false,
            dataType: 'json',
            url: `${config.orderPayPalBaseUrl}&rand=${new Date().getTime()}`,
            data: $(this).serialize(),
          });

          payPalCapturRequest.done(function(data) {
            if (undefined !== data.content) {
              let payPalOrderFetcher = new PayPalOrderFetcher(config);

              captureModal.on('hidden.bs.modal', function () {
                $(config.orderPayPalNotificationsContainer).append(payPalOrderNotification.createErrorHTMLElement({
                  text: data.content,
                  class: 'success',
                }));

                $(config.orderPayPalContainer).empty();
                $(config.orderPayPalLoaderContainer).show();

                payPalOrderFetcher.execute();
              });

              captureModal.modal('hide');
            }

            if (undefined !== data.errors) {
              for (const error of data.errors) {
                $(captureModalNotificationContainer).append(payPalOrderNotification.createErrorHTMLElement({
                  text: error,
                  class: 'danger',
                }));

                captureModalSubmitButton.prop('disabled', false);
              }
            }

            $(captureModalLoaderContainer).hide();
          });

          payPalCapturRequest.fail(function(jqXHR, textStatus, errorThrown) {
            $(captureModalNotificationContainer).append(payPalOrderNotification.createErrorHTMLElement({
              text: errorThrown,
              class: 'danger',
            }));

            if (undefined !== jqXHR.responseJSON && undefined !== jqXHR.responseJSON.content) {
              $(captureModalNotificationContainer).append(payPalOrderNotification.createErrorHTMLElement({
                text: jqXHR.responseJSON.content,
                class: 'danger',
              }));
            }

            $(captureModalLoaderContainer).hide();
          });
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
   * @param {string} config.orderPayPalModalVoidContainer - HTML element identifier
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
          event.preventDefault();
          const voidModal = $(this).parents(config.orderPayPalModalVoidContainer);
          const voidModalNotificationContainer = voidModal.find(config.orderPayPalModalNotificationsContainer);
          const voidModalLoaderContainer = voidModal.find(config.orderPayPalModalLoaderContainer);
          const voidModalSubmitButton = $(this).find('button[type="submit"]');
          const payPalOrderNotification = new PayPalOrderNotification(config);

          $(voidModalLoaderContainer).show();
          voidModalSubmitButton.prop('disabled', true);

          let payPalVoidRequest = $.ajax({
            type: 'POST',
            headers: {"cache-control": "no-cache"},
            cache: false,
            dataType: 'json',
            url: `${config.orderPayPalBaseUrl}&rand=${new Date().getTime()}`,
            data: $(this).serialize(),
          });

          payPalVoidRequest.done(function(data) {
            if (undefined !== data.content) {
              let payPalOrderFetcher = new PayPalOrderFetcher(config);

              voidModal.on('hidden.bs.modal', function () {
                $(config.orderPayPalNotificationsContainer).append(payPalOrderNotification.createErrorHTMLElement({
                  text: data.content,
                  class: 'success',
                }));

                $(config.orderPayPalContainer).empty();
                $(config.orderPayPalLoaderContainer).show();

                payPalOrderFetcher.execute();
              });

              voidModal.modal('hide');
            }

            if (undefined !== data.errors) {
              for (const error of data.errors) {
                $(voidModalNotificationContainer).append(payPalOrderNotification.createErrorHTMLElement({
                  text: error,
                  class: 'danger',
                }));

                voidModalSubmitButton.prop('disabled', false);
              }
            }

            $(voidModalLoaderContainer).hide();
          });

          payPalVoidRequest.fail(function(jqXHR, textStatus, errorThrown) {
            $(voidModalNotificationContainer).append(payPalOrderNotification.createErrorHTMLElement({
              text: errorThrown,
              class: 'danger',
            }));

            if (undefined !== jqXHR.responseJSON && undefined !== jqXHR.responseJSON.content) {
              $(voidModalNotificationContainer).append(payPalOrderNotification.createErrorHTMLElement({
                text: jqXHR.responseJSON.content,
                class: 'danger',
              }));
            }

            $(voidModalLoaderContainer).hide();
          });
        });
      });
    };
  };

  /**
   * @param {object} config
   * @param {string} config.orderPayPalNotificationsContainer - HTML element identifier
   * @constructor
   */
  let PayPalOrderNotification = function(config) {
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
