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

// Navigation guard
router.beforeEach((to, from, next) => {
  // if the merchant is onboarded, redirect to the next page
  if (store.state.firebase.onboardingCompleted
    && store.state.paypal.onboardingCompleted) {
    next();
  } else if (to.name === 'Authentication' // if the merchant is not onboarded: only autorize to navigate to authentication tab or help tab
    || to.name === 'Help'
    || to.name === 'Advanced') {
    next();
  } else { // redirect always on authentication tab if the merchant is trying to acces to an another tab
    next(from);
  }
});

export default router;
