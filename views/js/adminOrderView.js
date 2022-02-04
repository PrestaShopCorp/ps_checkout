'use strict';
/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License version 3.0
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
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

        if (undefined !== jqXHR.responseJSON && undefined !== jqXHR.responseJSON.errors) {
          for (const error of jqXHR.responseJSON.errors) {
            $(config.orderPayPalContainer).append(payPalOrderNotification.createErrorHTMLElement({
              text: error,
              class: 'danger',
            }));
          }
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
          refundModal.find(config.orderPayPalModalNotificationsContainer).empty();
          refundModal.find(config.orderPayPalModalLoaderContainer).hide();
          refundModal.modal('show');
        });

        $(document).on('change keyup', 'input[name="orderPayPalRefundAmount"]', function () {
          const refundModal = $(this).parents(config.orderPayPalModalContainer);
          const transactionCurrency = refundModal.find(config.orderPayPalRefundButtonValue).data('transaction-currency');
          const refundValue = $(this).val();

          refundModal.find(config.orderPayPalModalNotificationsContainer).empty();

          if (refundValue > 0) {
            refundModal.find(config.orderPayPalRefundSubmitButton).attr('disabled', false);
            refundModal.find(config.orderPayPalRefundButtonValue).text( `${refundValue} ${transactionCurrency}`);
          } else {
            refundModal.find(config.orderPayPalRefundSubmitButton).attr('disabled', true);
            refundModal.find(config.orderPayPalRefundButtonValue).text('');
          }
        });

        $(document).on('click', config.orderPayPalRefundSubmitButton, function () {
          const refundModal = $(this).parents(config.orderPayPalModalContainer);
          $('input[name="orderPayPalRefundAmount"]').attr('disabled', true);
          refundModal.find(config.orderPayPalRefundSubmitButton).attr('hidden', 'hidden');
          refundModal.find(config.orderPayPalRefundConfirmButton).attr('hidden', false);
        });

        $(document).on('click', '.modal.ps-checkout-refund [data-dismiss="modal"]', function () {
          const refundModal = $(this).parents(config.orderPayPalModalContainer);
          const refundAmountInput = $('input[name="orderPayPalRefundAmount"]');
          refundAmountInput.attr('disabled', false);
          refundAmountInput.val('');
          refundModal.find(config.orderPayPalRefundConfirmButton).attr('hidden', 'hidden');
          refundModal.find(config.orderPayPalRefundSubmitButton).attr('hidden', false);
          refundModal.find(config.orderPayPalRefundSubmitButton).attr('disabled', true);
          refundModal.find(config.orderPayPalRefundButtonValue).text('');
        });

        $(document).on('submit', config.orderPayPalModalRefundForm, function (event) {
          event.preventDefault();
          const refundModal = $(this).parents(config.orderPayPalModalContainer);
          const refundModalNotificationContainer = refundModal.find(config.orderPayPalModalNotificationsContainer);
          const refundModalLoaderContainer = refundModal.find(config.orderPayPalModalLoaderContainer);
          const refundModalSubmitButton = $(this).find('button[type="submit"]');
          const payPalOrderNotification = new PayPalOrderNotification(config);
          const refundAmountInput = $('input[name="orderPayPalRefundAmount"]');
          // Disabled input are excluded from formData
          refundAmountInput.attr('disabled', false);
          const formData = $(this).serialize();
          refundAmountInput.attr('disabled', true);

          $(refundModalNotificationContainer).empty();
          $(refundModalLoaderContainer).show();
          refundModalSubmitButton.prop('disabled', true);

          let payPalRefundRequest = $.ajax({
            type: 'POST',
            headers: {"cache-control": "no-cache"},
            cache: false,
            dataType: 'json',
            url: `${config.orderPayPalBaseUrl}&rand=${new Date().getTime()}`,
            data: formData,
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
            if (undefined !== errorThrown && errorThrown) {
              $(refundModalNotificationContainer).append(payPalOrderNotification.createErrorHTMLElement({
                text: errorThrown,
                class: 'danger',
              }));
            }

            if (undefined !== jqXHR.responseJSON && undefined !== jqXHR.responseJSON.errors) {
              jqXHR.responseJSON.errors.forEach(function (error) {
                $(refundModalNotificationContainer).append(payPalOrderNotification.createErrorHTMLElement({
                  text: error,
                  class: 'danger',
                }));
              });
            }

            if (undefined !== jqXHR.responseJSON && undefined !== jqXHR.responseJSON.content) {
              $(refundModalNotificationContainer).append(payPalOrderNotification.createErrorHTMLElement({
                text: jqXHR.responseJSON.content,
                class: 'danger',
              }));
            }

            if (undefined !== jqXHR.responseJSON && undefined !== jqXHR.responseJSON.errors) {
              for (const error of jqXHR.responseJSON.errors) {
                $(refundModalNotificationContainer).append(payPalOrderNotification.createErrorHTMLElement({
                  text: error,
                  class: 'danger',
                }));
              }
            }

            $(refundModalLoaderContainer).hide();
            refundModalSubmitButton.prop('disabled', false);
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

  let PayPalTransactions = function()
  {
    this.initialize = function() {
      $(document).on('click', '#ps_checkout button[role="tab"]', function () {
        let tabIdentifier = $(this).attr('aria-controls');
        switchDisplayedTab(tabIdentifier);
        switchSelectDropdown(tabIdentifier);
      });

      $(document).on('change', '#ps_checkout select#select-transaction', function() {
        let tabIdentifier = $(this).val();
        switchDisplayedTab(tabIdentifier)
      })

      function switchDisplayedTab(tabIdentifier)
      {
        $(`#ps_checkout button[role="tab"][aria-controls="${tabIdentifier}"]`).attr('aria-selected', true);
        $('#ps_checkout button[role="tab"]').not(`[aria-controls="${tabIdentifier}"]`).attr('aria-selected', false);
        $('#ps_checkout div[role="tabpanel"]').not(`#${tabIdentifier}`).attr('hidden', 'hidden');
        $(`#ps_checkout #${tabIdentifier}[role="tabpanel"]`).attr('hidden', false);
      }

      function switchSelectDropdown(tabIdentifier)
      {
        $('#ps_checkout select#select-transaction').val(tabIdentifier);
      }
    }
  }

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

    let payPalTransactions = new PayPalTransactions();
    payPalTransactions.initialize();
  };
})();
