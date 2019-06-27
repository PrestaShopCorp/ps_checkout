<template>
  <div>
    <div class="row">
      <div class="container">
        <HelpPanel module-key="bab535c26e031e9d224c0d128e063283" ps-version="1.6" iso-code="en" />
      </div>
    </div>
  </div>
</template>

<script>
  import HelpPanel from '@/components/panel/help';

  export default {
    name: 'Help',
    components: {
      HelpPanel,
    },
  };
</script>
