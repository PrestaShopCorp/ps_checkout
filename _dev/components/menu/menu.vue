<template>
  <div id="addons-menu" class="bootstrap">
    <div class="page-head custom-tab">
      <div class="page-head-tabs" id="head_tabs">
        <ul class="nav nav-pills">
          <slot />
        </ul>
      </div>
    </div>
  </div>
</template>

<script>
  export default {
    name: 'Menu',
  };
</script>

<style scoped lang="scss">
  .nav {
    height: 55px !important;
  }
  #content.bootstrap {
    padding-top: 160px;
  }
  #addons-menu {
    margin-bottom: 45px;
  }
  #addons-menu.bootstrap .page-head.custom-tab {
    height: auto !important;
    top: 8rem !important;
  }
  .mobile #addons-menu.bootstrap .page-head.custom-tab {
    top: 9rem !important;
  }
</style>
