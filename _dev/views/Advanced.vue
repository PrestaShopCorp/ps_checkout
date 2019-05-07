<template>

</template>

<script>
import {mapState} from 'vuex';

export default {
  name: 'Advanced',
  components: {
  },
  data() {
    return {
    };
  },
  computed: {
    ...mapState([
      'trans',
    ]),
  },
};
</script>
