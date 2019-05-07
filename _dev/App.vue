<template>
  <div id="app">
    <Menu>
      <MenuItem route="/authentication">{{ trans.menu.authentication }}</MenuItem>
      <MenuItem route="/customize">{{ trans.menu.customizeCheckout }}</MenuItem>
      <!-- <MenuItem route="/manage">{{ trans.menu.manageActivity }}</MenuItem>
      <MenuItem route="/advanced">{{ trans.menu.advancedSettings }}</MenuItem>
      <MenuItem route="/fees ">{{ trans.menu.fees }}</MenuItem> -->
      <!-- <MenuItem route="/help">{{ trans.menu.help }}</MenuItem> -->
    </Menu>
    <router-view />
  </div>
</template>

<script>
import Menu from '@/components/widgets/menu/Menu.vue';
import MenuItem from '@/components/widgets/menu/MenuItem.vue';
import {mapState} from 'vuex';

export default {
  name: 'Home',
  components: {
    Menu,
    MenuItem,
  },
  computed: {
    ...mapState([
      'trans',
    ]),
  },
  methods: {
    test() {
      return 'toto';
    },
  },
};
</script>
