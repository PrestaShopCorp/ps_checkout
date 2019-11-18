<!--**
 * 2007-2019 PrestaShop and Contributors
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License 3.0 (AFL-3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2019 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 * International Registered Trademark & Property of PrestaShop SA
 *-->
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

        <div class="card">
          <h3 class="card-header">
            <i class="material-icons">extension</i> Hooks
          </h3>
          <div class="card-block">
            <div class="card-text m-auto">
              <table>
                <tr v-for="hook in hooks" v-bind:key="hook.name">
                  <td v-if="hook.isRegistered">
                    <span class="text-success">
                      <i class="material-icons">check</i></span>
                  </td>
                  <td v-else>
                    <span class="text-danger">
                      <i class="material-icons">error_outline</i>
                    </span>
                  </td>
                  <td>{{ hook.name }}</td>
                </tr>
              </table>
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
      hooks() {
        return this.$store.state.context.hooks;
      },
      roundingSettingsIsCorrect() {
        return this.$store.getters.roundingSettingsIsCorrect;
      },
    },
  };
</script>
