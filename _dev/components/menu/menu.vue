<template>
  <div id="addons-menu" class="row bootstrap">
    <div class="page-head-tabs w-100">
      <ul class="nav nav-pills">
        <slot />
      </ul>
    </div>
  </div>
</template>

<script>
  export default {
    name: 'Menu',
  };
</script>

<style scoped>
  #addons-menu {
    width: 100%;
    position: fixed;
    margin-top: -22px;
    border-bottom: 1px solid #dfdfdf;
    z-index: 499;
  }
  .nav {
    height: 55px !important;
  }
</style>
