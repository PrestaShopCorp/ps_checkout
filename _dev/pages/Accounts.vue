<template>
  <div>
    <div class="row">
      <div class="container">
        <PSAlert :alert-type="ALERT_TYPE_WARNING">
          <h2>Approval pending</h2>
          <p>We are waiting for email confirmation… Check your inbox to finalize creation.</p>
          <p class="text-muted my-1">Didn’t receive any confirmation email?</p>
          <a class="btn btn-outline-secondary mt-1">Send email again</a>
        </PSAlert>
        <PSAlert :alert-type="ALERT_TYPE_WARNING">
          <h2>Documents needed</h2>
          <p>We need additional documents to complete our background check. Please prepare the following documents:</p>
          <ul class="my-1">
            <li><b>Photo IDs, such as driving licence, for all beneficial owners</b></li>
          </ul>
          <a class="btn btn-outline-secondary mt-1">Upload file</a>
        </PSAlert>
        <PSAlert :alert-type="ALERT_TYPE_WARNING">
          <h2>Your case is currently undergoing necessary background check.</h2>
          <p>
            This can take several days. If further information is needed, you will be notified.
            You can process <b>up to $500</b> in card transactions until your account is fully approved to accept card payment.
          </p>
          <div class="mt-3">
            <a href="#" target="_blank">
              Approval pending FAQs <i class="material-icons">arrow_right_alt</i>
            </a>
          </div>
        </PSAlert>
        <PSAlert :alert-type="ALERT_TYPE_DANGER">
          <h2>Account declined</h2>
          <p>
            We cannot process credit card payments for you at the moment.
            You can reapply after 90 days, in the meantine you can accept orders via PayPal.
          </p>
          <div class="mt-3">
            <a href="#" target="_blank">
              Account declined FAQs <i class="material-icons">arrow_right_alt</i>
            </a>
          </div>
        </PSAlert>
      </div>
    </div>
    <div class="row">
      <div class="container">
        <AccountList />
      </div>
    </div>
    <div v-if="$store.state.firebase.account.status === false || $store.state.paypal.account.status === false" class="row">
      <div class="container">
        <Reassurance />
      </div>
    </div>
  </div>
</template>

<script>
  import AccountList from '@/components/panel/account-list';
  import Reassurance from '@/components/block/reassurance';
  import PSAlert from '@/components/form/alert';
  import {ALERT_TYPE_DANGER, ALERT_TYPE_WARNING} from '@/lib/alert';

  export default {
    name: 'Accounts',
    components: {
      AccountList,
      Reassurance,
      PSAlert,
    },
    data() {
      return {
      };
    },
    computed: {
      ALERT_TYPE_DANGER: () => ALERT_TYPE_DANGER,
      ALERT_TYPE_WARNING: () => ALERT_TYPE_WARNING,
    },
  };
</script>
