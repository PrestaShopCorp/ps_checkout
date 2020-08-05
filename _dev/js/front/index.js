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
import "promise-polyfill/src/polyfill";
import "whatwg-fetch";
import "url-polyfill";

const configPayPalSdk = {
  id: "ps_checkoutPayPalSdkScript",
  namespace: "ps_checkoutPayPalSdkInstance",
  src: window.ps_checkoutPayPalSdkUrl,
  card3dsEnabled: window.ps_checkout3dsEnabled,
  cspNonce: window.ps_checkoutCspNonce,
  orderId: window.ps_checkoutPayPalOrderId,
  clientToken: window.ps_checkoutPayPalClientToken
};

/**
 * @param {object} configPayPalSdk
 * @param {string} configPayPalSdk.id
 * @param {string} configPayPalSdk.namespace
 * @param {string} configPayPalSdk.src
 * @param {string} configPayPalSdk.card3dsEnabled
 * @param {string} configPayPalSdk.cspNonce
 * @param {string} configPayPalSdk.orderId
 * @param {string} configPayPalSdk.clientToken
 */
const initPayPalSdk = configPayPalSdk => {
  const script = document.createElement("script");

  script.setAttribute("async", "");
  script.setAttribute("id", configPayPalSdk.id);
  script.setAttribute("src", configPayPalSdk.src);
  script.setAttribute("data-namespace", configPayPalSdk.namespace);

  if (configPayPalSdk.card3dsEnabled) {
    script.setAttribute("data-enable-3ds", "");
  }

  if (configPayPalSdk.cspNonce) {
    script.setAttribute("data-csp-nonce", configPayPalSdk.cspNonce);
  }

  if (configPayPalSdk.orderId) {
    script.setAttribute("data-order-id", configPayPalSdk.orderId);
  }

  if (configPayPalSdk.clientToken) {
    script.setAttribute("data-client-token", configPayPalSdk.clientToken);
  }

  document.head.appendChild(script);

  script.onload = () => {
    //@todo Init page handler
  };
};

if ("loading" === document.readyState) {
  document.addEventListener("DOMContentLoaded", () => {
    initPayPalSdk(configPayPalSdk);
  });
} else {
  initPayPalSdk(configPayPalSdk);
}
