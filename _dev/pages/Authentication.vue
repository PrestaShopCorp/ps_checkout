<template>
  <div id="authentication" class="d-flex">
    <div class="card container-fluid clearfix py-5 mr-4">
      <div class="align-item d-flex">
        <div class="connect-ps-content container-fluid">
          <div class="connect-ps-title">Connect to PrestaShop Services</div>
          <div class="connect-ps-text">
            <p class="mb-0">Activate all payment methods with your PrestaShop Services account.</p>
            <p>Create a new account or login with your current account.</p>
          </div>
          <div>
            <PSButton class="mr-3" ghost @click="logIn()">Log in</PSButton>
            <PSButton primary @click="signUp()">Create account</PSButton>
          </div>
        </div>
        <div class="connect-ps-image mr-5">
          <img src="@/assets/images/ps-services-account.png" alt="">
        </div>
      </div>
    </div>
    <Reassurance />
  </div>
</template>

<script>
import {mapState} from 'vuex';
import PSButton from '@/components/form/button';
import Reassurance from '@/components/block/reassurance';

export default {
  name: 'Authentication',
  components: {
    PSButton,
    Reassurance,
  },
  computed: {
    ...mapState([
      'trans',
    ]),
  },
  methods: {
    signUp() {
      this.$router.push('/authentication/signup');
    },
    logIn() {
      this.$router.push('/authentication/login');
    },
  },
};
</script>

<style scoped>
.d-flex {
  align-items: flex-start;
}
.align-item {
  align-items: center;
}
.connect-ps-content {
  text-align: center;
}
.connect-ps-title {
  color: #363A41;
  font-size: 32px;
  line-height: 43px;
  padding-bottom: 25px;
}
.connect-ps-text {
  color: #363A41;
  font-size: 16px;
  line-height: 24px;
  padding-bottom: 25px;
}
</style>
