import Vue from 'vue';
import Router from 'vue-router';

import Signup from '@/pages/Signup.vue';
import Login from '@/pages/Login.vue';
import Customize from '@/pages/Customize.vue';
import Accounts from '@/pages/Accounts.vue';
import Activity from '@/pages/Activity.vue';
import Advanced from '@/pages/Advanced.vue';
import Help from '@/pages/Help.vue';

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
      component: Accounts,
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
      path: '/customize',
      name: 'Customize',
      component: Customize,
    },
    {
      path: '/activity',
      name: 'Activity',
      component: Activity,
    },
    {
      path: '/advanced',
      name: 'Advanced',
      component: Advanced,
    },
    {
      path: '/help',
      name: 'Help',
      component: Help,
    },
  ],
});
