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
    .then(res => res.data)
    .catch((error) => {
      console.log(error);
    });
}
