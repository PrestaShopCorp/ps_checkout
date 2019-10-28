<template>
  <div>
    <div class="row">
      <div class="container">
        <div class="card">
          <h3 class="card-header">
            <i class="material-icons">warning</i> Debug view
          </h3>
          <div class="card-block">
            <div class="card-text m-auto">
              <ul>
                <li>PrestaShop version: <b>{{ psVersion }}</b></li>
                <li>Module version: <b>{{ moduleVersion }}</b></li>
                <li>Shop ID: <b>{{ shopId }}</b></li>
                <li>Rounding config: <b>{{ roundingSettingsIsCorrect }}</b></li>
              </ul>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
  export default {
    name: 'Debug',
    computed: {
      moduleVersion() {
        return this.$store.state.context.moduleVersion;
      },
      psVersion() {
        return this.$store.state.context.psVersion;
      },
      shopId() {
        return this.$store.state.context.shopId;
      },
      roundingSettingsIsCorrect() {
        return this.$store.getters.roundingSettingsIsCorrect;
      },
    },
  };
</script>
