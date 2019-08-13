import Vue from 'vue';
import Router from 'vue-router';
import store from './store';

import Customize from '@/pages/Customize.vue';
import Accounts from '@/pages/Accounts.vue';
import Activity from '@/pages/Activity.vue';
import Advanced from '@/pages/Advanced.vue';
import Help from '@/pages/Help.vue';

Vue.use(Router);

const router = new Router({
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
      path: '/debugDoNotTouch',
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

// TODO: Make a navigation guard to limit the user to the authentification tab if he does not
// complete the paypal and firebase onboarding
// router.beforeEach((to, from, next) => {
// });

export default router;
