import axios from 'axios';
import {forEach} from 'lodash';

const api = axios.create({
  baseURL: window.prestashopCheckoutAjax,
});

export default function ajax(params) {
  const form = new FormData();
  form.append('ajax', true);
  form.append('action', params.action);
  form.append('controller', 'AdminAjaxPrestashopCheckout');

  forEach(params.data, (value, key) => {
    form.append(key, value);
  });

  return api.post('', form)
    .then(res => res.data)
    .catch((error) => {
      console.log(error);
    });
}
