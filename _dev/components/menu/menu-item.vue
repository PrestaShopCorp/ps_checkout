<template>
  <li>
    <router-link class="menu-item" :to="route">
      <slot />
    </router-link>
  </li>
</template>

<script>
  export default {
    name: 'MenuItem',
    props: {
      route: {
        type: String,
        default: '',
      },
    },
    methods: {

    },
  };
</script>

<!-- Add "scoped" attribute to limit CSS to this component only -->
<style scoped lang="scss">
  a {
    color: #42b983;
    &.router-link-exact-active {
      border-bottom: 3px solid #25b9d7;
      background-color: #f4f9fb;
      color: #363a41 !important;
    }
    &.router-link-exact-active:hover {
      color: #25b9d7;
    }
    &.router-link-active {
      border-bottom: 3px solid #25b9d7;
      background-color: #f4f9fb;
      color: #363a41 !important;
    }
    &.menu-item:hover:not(.router-link-exact-active) {
      color: #25b9d7 !important;
    }
  }
</style>
