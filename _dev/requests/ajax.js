import axios from 'axios';
import {forEach} from 'lodash';

const ajax = axios.create({
  baseURL: prestashopPaymentsAjax,
});

export function request(params) {
  const form = new FormData();
  form.append('ajax', true);
  form.append('action', params.action);
  form.append('controller', 'AdminAjaxPrestashopPayments');

  forEach(params.data, (value, key) => {
    form.append(key, value);
  });

  return ajax.post('', form)
    .then(res => res.data)
    .catch((error) => {
      console.log(error);
    });
}

export function getFaq(moduleKey, psVersion, isoCode) {
  return ajax.post(`http://api.addons.prestashop.com/request/faq/${moduleKey}/${psVersion}/${isoCode}`)
    .then(res => res.data)
    .catch((error) => {
      console.log(error);
    });
}
