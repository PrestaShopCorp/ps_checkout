{extends file='page.tpl'}
{block name='content'}
  <div class="ps-checkout wrapper">
    <div class="ps-checkout content">
      <div class="alert alert-danger">
        {if isset($error)}
          {$error}
        {else}
          {l s='3DS verification failed, please try again.' mod='ps_checkout'}
        {/if}
      </div>
      <div class="ps-checkout order-link">
        <a href="{$order_url}">{l s='Back to order page' mod='ps_checkout'}</a>
      </div>
    </div>
  </div>
{/block}
{block name='notifications'}
{/block}
{block name='header'}
{/block}
{block name="footer"}
{/block}
