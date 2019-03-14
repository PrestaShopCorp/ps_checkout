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
    if (typeof paypalOrderId === 'undefined') {
        return
    }

    initHostedFields()
    initSmartButtons()
})

function initSmartButtons() {
    paypal.Buttons({
        createOrder: function() {
            return paypalOrderId
        },
        onApprove: function(payload) {
            window.location.replace(orderValidationLink + '?orderId=' + payload.orderID);
        }
    }).render('#paypal-button-container')
}

function initHostedFields() {
    //check whether hosted fields is eligible for that Partner Account
    if (paypal.HostedFields.isEligible())
    {
        // render hosted fields
        paypal.HostedFields.render({
            createOrder: function () {
                return paypalOrderId
            },
            styles: {
                'input': {
                    'height': '25px',
                    'font-size': '1rem',
                },
                ':focus': {
                    'border-color': 'red'
                }
            },
            fields: {
                number: {
                    selector: '#card-number',
                    placeholder: 'Card number',
                    class: 'form-control'
                },
                cvv: {
                    selector: '#cvv',
                    placeholder: 'XXX'
                },
                expirationDate: {
                    selector: '#expiration-date',
                    placeholder: 'MM/YYYY'
                }
            }
        }).then(function (hf) {

            hf.on('cardTypeChange', function (event) {
                console.log(event.cards[0].type)
                // Change card bg depending on card type
                if (event.cards.length === 1) {
                    // $(form).removeClass().addClass(event.cards[0].type);
                    $('#card-image').removeClass().addClass(event.cards[0].type)
                    $('header').addClass('header-slide')

                    // Change the CVV length for AmericanExpress cards
                    if (event.cards[0].code.size === 4) {
                        hf.setAttribute({
                            field: 'cvv',
                            attribute: 'placeholder',
                            value: 'XXXX'
                        });
                    }
                } else {
                    $('#card-image').removeClass()
                    hf.setAttribute({
                        field: 'cvv',
                        attribute: 'placeholder',
                        value: 'XXX'
                    });
                }
            });


            $('#hosted-fields-form').submit(function (event) {
                event.preventDefault();

                // TODO : Patch a first time the order to prevent any modifications of the cart

                hf.submit({
                    contingencies: ['3D_SECURE'] // only necessary if using 3D Secure verification
                }).then(function (payload) {

                    console.log(payload)

                    if (payload.liabilityShifted === undefined) { // No 3DS Contingency Passed or card not enrolled to 3ds
                        window.location.replace(orderValidationLink + '?orderId=' + payload.orderId);
                        console.log('undefined')
                    }

                    if (payload.liabilityShifted) { // 3DS Contingency Passed - Buyer confirmed Successfully
                        window.location.replace(orderValidationLink + '?orderId=' + payload.orderId);
                        console.log('success')
                    }

                    if (payload.liabilityShifted === false) { // 3DS Contingency Passed, but Buyer skipped 3DS
                        // window.location.replace(orderValidationLink + '?orderId=' + payload.orderId);
                        console.log('error')
                    }
                }).catch(function (err) {
                    document.getElementById("consoleLog").innerHTML = JSON.stringify(err);
                    console.log(err)
                });
            });
        });
    }
}
