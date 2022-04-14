/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License 3.0 (AFL-3.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 */
import Vue from 'vue';
import Router from 'vue-router';
import store from './store';

import Customize from '@/pages/Customize';
import Accounts from '@/pages/Accounts';
import Signin from '@/pages/Signin';
import Signup from '@/pages/Signup';
import ResetPassword from '@/pages/ResetPassword';
import PsxAdditionalDetails from '@/pages/PsxAdditionalDetails';
import Activity from '@/pages/Activity';
import Advanced from '@/pages/Advanced';
import Experimental from '@/pages/Experimental';
import Debug from '@/pages/Debug';
import Help from '@/pages/Help';

Vue.use(Router);

const router = new Router({
  routes: [
    {
      path: '/',
      redirect: '/authentication',
      meta: {
        exclude: true
      }
    },
    {
      path: '/authentication',
      name: 'Click configure',
      meta: {
        exclude: true
      },
      component: Accounts,
      beforeEnter: (to, from, next) => {
        if (
          (store.getters.onboarding &&
            store.getters.firebaseOnboardingIsCompleted &&
            !store.getters.paypalOnboardingIsCompleted &&
            !store.getters.psxOnboardingIsCompleted) ||
          store.getters.businessDataCheck
        ) {
          next('/authentication/additional');
        } else {
          next();
        }
      }
    },
    {
      path: '/authentication/signin',
      name: 'Click PS Account Login',
      component: Signin,
      beforeEnter: (to, from, next) => {
        if (
          store.getters.firebaseOnboardingIsCompleted ||
          store.getters.hasNewMajorVersionAvailable
        ) {
          next(from);
        } else {
          next();
        }
      }
    },
    {
      path: '/authentication/signup',
      name: 'Click PS Account Sign Up',
      component: Signup,
      beforeEnter: (to, from, next) => {
        if (
          store.getters.firebaseOnboardingIsCompleted ||
          store.getters.hasNewMajorVersionAvailable
        ) {
          next(from);
        } else {
          next();
        }
      }
    },
    {
      path: '/authentication/reset',
      name: 'ResetPassword',
      component: ResetPassword,
      beforeEnter: (to, from, next) => {
        if (store.getters.firebaseOnboardingIsCompleted) {
          next(from);
        } else {
          next();
        }
      }
    },
    {
      path: '/authentication/additional',
      name: 'View Form Biz',
      component: PsxAdditionalDetails
    },
    {
      path: '/customize',
      name: 'View Customize Checkout Tab',
      component: Customize,
      beforeEnter: (to, from, next) => {
        if (store.getters.businessDataCheck) {
          next('/authentication/additional');
        } else {
          next();
        }
      }
    },
    {
      path: '/activity',
      name: 'View Manage Activity Tab',
      component: Activity,
      beforeEnter: (to, from, next) => {
        if (store.getters.businessDataCheck) {
          next('/authentication/additional');
        } else {
          next();
        }
      }
    },
    {
      path: '/debug',
      name: 'Debug',
      component: Debug
    },
    {
      path: '/experimental',
      name: 'Experimental',
      component: Experimental
    },
    {
      path: '/advanced',
      name: 'View Advanced Settings Tab',
      component: Advanced,
      beforeEnter: (to, from, next) => {
        if (store.getters.businessDataCheck) {
          next('/authentication/additional');
        } else {
          next();
        }
      }
    },
    {
      path: '/help',
      name: 'View Help Tab',
      component: Help
    }
  ]
});

// Page list accesible by guest customer
const guestPages = [
  '/authentication',
  '/authentication/additional',
  '/authentication/signin',
  '/authentication/signup',
  '/authentication/reset',
  '/experimental',
  '/debug',
  '/help'
];

// Global navigation guard
router.beforeEach((to, from, next) => {
  // if the merchant is onboarded, redirect to the next page
  if (store.getters.merchantIsFullyOnboarded) {
    next();
  } else if (guestPages.indexOf(to.path) !== -1) {
    // if the merchant is not onboarded: only autorize to navigate to authentication tab or help tab
    next();
  } else {
    // redirect always on the previous tab if the merchant is trying to acces to an another tab
    next(from);
  }
});

export default router;
