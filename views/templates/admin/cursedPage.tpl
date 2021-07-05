{**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License version 3.0
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
 *}
<!DOCTYPE html>
<!--[if lt IE 7]> <html lang="{$iso|escape:'html':'UTF-8'}" class="no-js lt-ie9 lt-ie8 lt-ie7 lt-ie6"> <![endif]-->
<!--[if IE 7]>    <html lang="{$iso|escape:'html':'UTF-8'}" class="no-js lt-ie9 lt-ie8 ie7"> <![endif]-->
<!--[if IE 8]>    <html lang="{$iso|escape:'html':'UTF-8'}" class="no-js lt-ie9 ie8"> <![endif]-->
<!--[if gt IE 8]> <html lang="{$iso|escape:'html':'UTF-8'}" class="no-js ie9"> <![endif]-->
<html lang="{$iso|escape:'html':'UTF-8'}">
<head>
  <meta charset="utf-8">

  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0">
  <meta name="apple-mobile-web-app-capable" content="yes">
  <link rel="icon" type="image/x-icon" href="{$img_dir|escape:'html':'UTF-8'}favicon.ico"/>
  <link rel="apple-touch-icon" href="{$img_dir|escape:'html':'UTF-8'}app_icon.png"/>
  <meta name="robots" content="NOFOLLOW, NOINDEX">
  <title>
      {$shop_name|escape:'html':'UTF-8'} {$navigationPipe|escape:'html':'UTF-8'} {$meta_title|escape:'html':'UTF-8'}
  </title>
    {foreach from=$css_files key=css_uri item=media}
      <link href="{$css_uri}" rel="stylesheet" type="text/css" media="{$media}"/>
    {/foreach}
    {if (isset($js_def) && count($js_def) || isset($js_files) && count($js_files))}
        {include file=$smarty.const._PS_ALL_THEMES_DIR_|cat:"javascript.tpl"}
    {/if}
</head>
<body class="ps_back-office bootstrap">
<div id="login">
  <div id="content">
    <div id="login-panel">
      <div class="flip-container">
        <div class="flipper">
          <div class="front front_login panel">
            <p class="text-center">
              <img src="{$logoSrc|escape:'html':'UTF-8'}" alt="{$meta_title|escape:'html':'UTF-8'}" class="img-thumbnail" height="100" width="100">
            </p>
            <h4 id="shop_name">{$meta_title|escape:'html':'UTF-8'}</h4>
              {if $errors}
                <div class="alert alert-danger">
                    {if count($errors) == 1}
                        {$errors[0]|escape:'html':'UTF-8'}
                    {else}
                      <ul>
                          {foreach $errors as $error}
                            <li>{$error|escape:'html':'UTF-8'}</li>
                          {/foreach}
                      </ul>
                    {/if}
                </div>
              {/if}
            <p class="text-center">
                {l s='An error occurred on PayPal Onboarding, please try again.' mod='ps_checkout'}
            </p>
            <p class="text-center">
              <a href="{$moduleLink|escape:'html':'UTF-8'}" class="btn btn-primary">
                  {l s='Go back to module configuration' mod='ps_checkout'}
              </a>
            </p>
          </div>
        </div>
      </div>
    </div>
  </div>
</body>
</html>
