<template>
  <div id="app">
    <Menu>
      <MenuItem route="/authentication">{{ $t('menu.authentication') }}</MenuItem>
      <MenuItem route="/customize">{{ $t('menu.customizeCheckout') }}</MenuItem>
      <!-- <MenuItem route="/manage">{{ trans.menu.manageActivity }}</MenuItem>
      <MenuItem route="/advanced">{{ trans.menu.advancedSettings }}</MenuItem>
      <MenuItem route="/fees ">{{ trans.menu.fees }}</MenuItem> -->
      <!-- <MenuItem route="/help">{{ trans.menu.help }}</MenuItem> -->
    </Menu>
    <router-view />
  </div>
</template>

<script>
import Menu from '@/components/menu/menu';
import MenuItem from '@/components/menu/menu-item';

export default {
  name: 'Home',
  components: {
    Menu,
    MenuItem,
  },
};
</script>
