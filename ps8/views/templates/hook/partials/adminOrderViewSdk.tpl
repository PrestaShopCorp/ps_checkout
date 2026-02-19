{**
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
 *}
<script>
  (function () {
    try {
      var containerId = '{$containerId|escape:'javascript':'UTF-8'}';
      var container = document.getElementById(containerId);

      if (!container) {
        console.error('[ps_checkout] PrestaShopCheckout container #' + containerId + ' not found.');
        return;
      }

      if (typeof window.PrestaShopCheckoutSDK === 'undefined' || typeof window.PrestaShopCheckoutSDK.PrestaShopCheckout === 'undefined') {
        throw new Error('PrestaShopCheckout SDK not available on window.');
      }

      var context = (window.store && window.store.context) ? window.store.context : {};
      var ajaxUrl = context.prestashopCheckoutAjax || '';

      // Placeholder data matching the SDK's expected shape (real presenter mapping is a follow-up).
      var orderData = {
        // reference: 'ORDER-' + (context.orderId || ''),
        total: 125.5,
        currency: 'EUR',
        status: 'PENDING',
        balance: 0,
        paymentMode: 'PAYPAL',
        isTestMode: true,
        threeDSecure: 'SUCCESS',
        liabilityShift: 'BANK',
        financials: {
          gross: 125.5,
          fees: 0,
          net: 125.5,
          captured: 0,
          leftToCapture: 125.5,
        },
      };

      var transactionList = [
        {
          id: 'TX-PLACEHOLDER',
          type: 'AUTHORIZATION',
          status: 'PENDING',
          date: new Date().toISOString(),
          amount: 125.5,
          currency: 'EUR',
          reference: 'TX-PLACEHOLDER',
          details: {
            total: 125.5,
            sellerProtection: 'ELIGIBLE',
          },
        },
      ];

      var actionMap = {
        capture: 'CaptureAuthorization',
        void: 'VoidAuthorization',
        reauthorize: 'ReauthorizeAuthorization',
        refund: 'RefundOrder',
      };

      function onSubmit(type, transaction, data) {
        console.log('[ps_checkout] onSubmit:', type, transaction, data);
        var action = actionMap[type];
        if (!action || !ajaxUrl) {
          console.warn('[ps_checkout] Unknown action type or missing AJAX URL:', type);
          return;
        }
        var payload = Object.assign({ id_transaction: transaction.id }, data || {});
        fetch(ajaxUrl + '&action=' + action, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(payload),
        })
          .then(function (res) { return res.json(); })
          .then(function (json) { console.log('[ps_checkout] AJAX response:', json); })
          .catch(function (err) { console.error('[ps_checkout] AJAX error:', err); });
      }

      var checkoutComponent = window.PrestaShopCheckoutSDK.PrestaShopCheckout({
        orderData: orderData,
        transactionList: transactionList,
        onSubmit: onSubmit,
      });

      checkoutComponent.render('#' + containerId);
      console.log('[ps_checkout] PrestaShopCheckout component rendering into #' + containerId);

    } catch (e) {
      console.error('[ps_checkout] PrestaShopCheckout initialization failed:', e);
      var container = document.getElementById('{$containerId|escape:'javascript':'UTF-8'}' || 'ps-checkout-merchant-order-view');
      if (container) {
        var msg = document.createElement('div');
        msg.className = 'd-print-none alert alert-warning';
        msg.textContent = 'PrestaShop Checkout order view could not be loaded. See browser console for details.';
        container.appendChild(msg);
      }
    }
  })();
</script>
