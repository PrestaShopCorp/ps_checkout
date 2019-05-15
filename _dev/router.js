import Vue from 'vue';
import Router from 'vue-router';

import Authentication from '@/pages/Authentication.vue';
import PaypalAuth from '@/pages/PaypalAuth.vue';
import Signup from '@/pages/Signup.vue';
import Login from '@/pages/Login.vue';
import Customize from '@/pages/Customize.vue';
// import Manage from '@/pages/Manage.vue';
// import Advanced from '@/pages/Advanced.vue';
// import Fees from '@/pages/Fees.vue';
// import Help from '@/pages/Help.vue';

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
