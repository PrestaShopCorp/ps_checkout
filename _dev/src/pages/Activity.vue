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
    <b-container class="mb-4">
      <Dispute />
    </b-container>

    <b-container>
      <FeatureIncoming />
    </b-container>
  </div>
</template>

<script>
  import Dispute from '@/components/block/dispute';
  import FeatureIncoming from '@/components/block/feature-incoming';

  export default {
    name: 'Activity',
    components: {
      Dispute,
      FeatureIncoming,
    },
  };
</script>
