<template>
  <li class="nav-item">
    <router-link active-class="active" class="nav-link" :to="route">
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

<style scoped>
  a {
    border-radius: unset !important;
  }
</style>
