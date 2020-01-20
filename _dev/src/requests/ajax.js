/**
 * 2007-2019 PrestaShop and Contributors
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License 3.0 (AFL-3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2019 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 * International Registered Trademark & Property of PrestaShop SA
 */
import axios from 'axios';
import {forEach} from 'lodash';

export default function ajax(params) {
  const form = new FormData();
  form.append('ajax', true);
  form.append('action', params.action);

  form.append('controller', 'AdminAjaxPrestashopCheckout');

  forEach(params.data, (value, key) => {
    form.append(key, value);
  });

  return axios.post(params.url, form)
    .then((res) => res.data)
    .catch((error) => {
      // eslint-disable-next-line no-console
      console.log(error);
    });
}
