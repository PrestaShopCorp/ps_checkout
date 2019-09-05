import Vue from 'vue';
import Router from 'vue-router';
import store from './store';

import Customize from '@/pages/Customize';
import Accounts from '@/pages/Accounts';
import Signin from '@/pages/Signin';
import Signup from '@/pages/Signup';
import PsxAdditionalDetails from '@/pages/PsxAdditionalDetails';
import Activity from '@/pages/Activity';
import Advanced from '@/pages/Advanced';
import Help from '@/pages/Help';

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
      beforeEnter: (to, from, next) => {
        if (store.getters.firebaseOnboardingIsCompleted
          && !store.getters.paypalOnboardingIsCompleted
          && !store.getters.psxOnboardingIsCompleted) {
          next('/authentication/additional');
        } else {
          next();
        }
      },
    },
    {
      path: '/authentication/signin',
      name: 'Signin',
      component: Signin,
      beforeEnter: (to, from, next) => {
        if (store.getters.firebaseOnboardingIsCompleted) {
          next(from);
        } else {
          next();
        }
      },
    },
    {
      path: '/authentication/signup',
      name: 'Signup',
      component: Signup,
      beforeEnter: (to, from, next) => {
        if (store.getters.firebaseOnboardingIsCompleted) {
          next(from);
        } else {
          next();
        }
      },
    },
    {
      path: '/authentication/additional',
      name: 'PsxAdditionalDetails',
      component: PsxAdditionalDetails,
      beforeEnter: (to, from, next) => {
        if (!store.getters.firebaseOnboardingIsCompleted
          || store.getters.psxOnboardingIsCompleted) {
          next('/authentication');
        } else {
          next();
        }
      },
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

// Page list accesible by guest customer
const guestPages = [
  'Authentication',
  'PsxAdditionalDetails',
  'Signin',
  'Signup',
  'Help',
  'Advanced',
];

// Global navigation guard
router.beforeEach((to, from, next) => {
  // if the merchant is onboarded, redirect to the next page
  if (store.getters.merchantIsFullyOnboarded) {
    next();
  } else if (guestPages.indexOf(to.name) !== -1) {
    // if the merchant is not onboarded: only autorize to navigate to authentication tab or help tab
    next();
  } else {
    // redirect always on the previous tab if the merchant is trying to acces to an another tab
    next(from);
  }
});

export default router;
