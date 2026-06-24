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
'use strict';

var ps_checkout_merchant = {};

(function () {
  ps_checkout_merchant.initialize = function (config) {
    try {
      var containerId = config.containerId;
      var ajaxUrl    = config.ajaxUrl;
      var orderId    = config.orderId;
      var locale     = config.locale;

      var container = document.getElementById(containerId);
      if (!container) {
        console.error('[ps_checkout] PrestaShopCheckout container #' + containerId + ' not found.');
        return;
      }

      if (
        typeof window.PrestaShopCheckoutSDK === 'undefined' ||
        typeof window.PrestaShopCheckoutSDK.PrestaShopCheckout === 'undefined'
      ) {
        throw new Error('PrestaShopCheckout SDK not available on window.');
      }

      var sdkScript = Array.from(document.querySelectorAll('script[src]')).find(function (s) {
        return s.src.indexOf('merchant-sdk') !== -1;
      });
      var appUrl = sdkScript ? new URL(sdkScript.src).origin : undefined;

      var checkoutComponent = null;

      var actionMap = {
        capture:     'CaptureAuthorization',
        void:        'VoidAuthorization',
        reauthorize: 'ReauthorizeAuthorization',
        refund:      'RefundOrder',
      };

      function onSubmit(type, transactionId, data) {
        var action = actionMap[type];
        if (!action || !ajaxUrl) {
          return Promise.reject(new Error('Unknown action type or missing AJAX URL'));
        }
        var params = new URLSearchParams({
          ajax: 1,
          action: action,
          id_order: orderId,
          id_transaction: transactionId,
        });
        if (data) {
          Object.keys(data).forEach(function (key) {
            params.append(key, data[key]);
          });
        }
        return fetch(ajaxUrl, {
          method: 'POST',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
          body: params.toString(),
        })
          .then(function (res) { return res.json(); })
          .then(function (json) {
            if (!json.status) {
              throw new Error((json.errors || []).join(', ') || (json.error && json.error.message) || 'Unknown error');
            }
            if (checkoutComponent && json.order) {
              checkoutComponent.updateProps({
                order: json.order,
              });
            }
            return { message: json.message || '' };
          });
      }

      container.innerHTML = '<div class="d-print-none text-muted p-3">Loading PayPal order data\u2026</div>';

      fetch(ajaxUrl + '&rand=' + new Date().getTime(), {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'cache-control': 'no-cache' },
        body: new URLSearchParams({ ajax: 1, action: 'GetOrderViewData', id_order: orderId }).toString(),
      })
        .then(function (res) { return res.json(); })
        .then(function (json) {
          if (!json.status) {
            throw new Error((json.errors || []).join(', ') || 'Unknown error');
          }

          container.innerHTML = '';

          checkoutComponent = window.PrestaShopCheckoutSDK.PrestaShopCheckout({
            url: appUrl,
            order: json.order,
            isTestMode: json.isTestMode || false,
            locale: locale,
            onSubmit: onSubmit,
          });
          checkoutComponent.render('#' + containerId);
        })
        .catch(function (err) {
          console.error('[ps_checkout] Failed to load PayPal order data:', err);
          container.innerHTML = '';
          var msg = document.createElement('div');
          msg.className = 'd-print-none alert alert-warning';
          msg.textContent = 'PrestaShop Checkout order view could not be loaded. See browser console for details.';
          container.appendChild(msg);
        });

    } catch (e) {
      console.error('[ps_checkout] PrestaShopCheckout initialization failed:', e);
      var container = document.getElementById(config.containerId || 'ps-checkout-merchant-order-view');
      if (container) {
        var msg = document.createElement('div');
        msg.className = 'd-print-none alert alert-warning';
        msg.textContent = 'PrestaShop Checkout order view could not be loaded. See browser console for details.';
        container.appendChild(msg);
      }
    }
  };
})();
