import axios from 'axios';
import {forEach} from 'lodash';

const api = axios.create({
  baseURL: prestashopPaymentsAjax,
});

export function ajax(params) {
  const form = new FormData();
  form.append('ajax', true);
  form.append('action', params.action);
  form.append('controller', 'AdminAjaxPrestashopPayments');

  forEach(params.data, (value, key) => {
    form.append(key, value);
  });

  return api.post('', form)
    .then(res => res.data)
    .catch((error) => {
      console.log(error);
    });
}

export function getFaq(moduleKey, psVersion, isoCode) {
  return api.post(`http://api.addons.prestashop.com/request/faq/${moduleKey}/${psVersion}/${isoCode}`)
    .then(res => res.data)
    .catch((error) => {
      console.log(error);
    });
}
