import Vue from 'vue';
import Router from 'vue-router';

import Authentication from '@/views/Authentication.vue';
import PaypalAuth from '@/views/PaypalAuth.vue';
import Signup from '@/views/Signup.vue';
import Login from '@/views/Login.vue';
import Customize from '@/views/Customize.vue';
// import Manage from '@/views/Manage.vue';
// import Advanced from '@/views/Advanced.vue';
// import Fees from '@/views/Fees.vue';
// import Help from '@/views/Help.vue';

Vue.use(Router);

export default new Router({
  routes: [
    {
      path: '/',
      redirect: '/authentication',
    },
    {
      path: '/authentication',
      name: 'Authentication',
      component: Authentication,
    },
    {
      path: '/authentication/login',
      name: 'Login',
      component: Login,
    },
    {
      path: '/authentication/signup',
      name: 'Signup',
      component: Signup,
    },
    {
      path: '/authentication/paypal',
      name: 'PaypalAuth',
      component: PaypalAuth,
    },
    {
      path: '/customize',
      name: 'Customize',
      component: Customize,
    },
    // {
    //   path: '/manage',
    //   name: 'Manage',
    //   component: Manage,
    // },
    // {
    //   path: '/advanced',
    //   name: 'Advanced',
    //   component: Advanced,
    // },
    // {
    //   path: '/fees',
    //   name: 'Fees',
    //   component: Fees,
    // },
    // {
    //   path: '/help',
    //   name: 'Help',
    //   component: Help,
    // },
  ],
});
