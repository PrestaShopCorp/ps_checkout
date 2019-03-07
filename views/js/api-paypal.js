/**
* 2007-2019 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author    PrestaShop SA <contact@prestashop.com>
*  @copyright 2007-2019 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*
* Don't forget to prefix your containers with your own identifier
* to avoid any conflicts with others containers.
*/

$(document).ready(function() {
    console.log(paypalOrderId)
    initHostedFields()
    // $.ajax(getAccessToken).done(response => {
    //     $.ajax(getClientToken(response.access_token)).done(response => {
    //         $('#paypalSdk').attr('data-client-token', response.client_token)
    //         initHostedFields()
    //     })
    // })

})

function initHostedFields() {
    //check whether hosted fields is eligible for that Partner Account
    if (paypal.HostedFields.isEligible())
    {
        // render hosted fields
        paypal.HostedFields.render({
            createOrder: function () {
                return paypalOrderId
                // return paypalOrderId
            },
            // Insert your Order ID here that you receive from your Create Order API call
            // styles: {
            //     'input': {
            //         'font-size': '1rem',
            //     },
            //     ':focus': {
            //     }
            // },
            fields: {
                number: {
                    selector: '#card-number',
                    placeholder: 'card number',
                    class: 'form-control'
                },
                cvv: {
                    selector: '#cvv',
                    placeholder: 'card security number'
                },
                expirationDate: {
                    selector: '#expiration-date',
                    placeholder: 'mm/yyyy'
                }
            }
        }).then(function (hf) {
            $('#hosted-fields-form').submit(function (event) {
                event.preventDefault();

                // TODO : Patch a first time the order to prevent any modifications of the cart

                hf.submit({
                    // contingencies: ['3D_SECURE'] // only necessary if using 3D Secure verification
                }).then(function (payload) {

                    console.log(payload)

                    if (payload.liabilityShifted === undefined) {
                        // No 3DS Contingency Passed or card not enrolled to 3ds
                        window.location.replace(orderValidationLink);
                        // $(this).submit();
                        console.log('undefined')
                    }

                    if (payload.liabilityShifted) {
                        // 3DS Contingency Passed - Buyer confirmed Successfully
                        window.location.replace(orderValidationLink);
                        // $(this).submit();
                        console.log('success')
                    }

                    if (payload.liabilityShifted === false) {
                        // 3DS Contingency Passed, but Buyer skipped 3DS
                        window.location.replace(orderValidationLink);
                        // $(this).submit();
                        console.log('error')
                    }
                }).catch(function (err) {
                    console.log(err);
                    document.getElementById("consoleLog").innerHTML = JSON.stringify(err);
                });
            });
        });
    }
}


let getAccessToken = {
    'async': true,
    'crossDomain': true,
    'url': 'https://api.sandbox.paypal.com/v1/oauth2/token',
    'method': 'POST',
    'headers': {
        'authorization': 'Basic '  + btoa('<username>' + ':' + '<password>'),
        'content-type': 'application/x-www-form-urlencoded'
    },
    'data': {
        'grant_type': 'client_credentials'
    }
}

let getClientToken = accessToken => {
    return {
        'async': true,
        'crossDomain': true,
        'url': 'https://api.sandbox.paypal.com/v1/identity/generate-token',
        'method': 'POST',
        'headers': {
          'authorization': 'Bearer ' + accessToken,
          'content-type': 'application/json'
        },
        'data': {}
    }
}


