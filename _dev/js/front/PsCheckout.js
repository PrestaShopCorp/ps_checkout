/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License 3.0 (AFL-3.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 */

/**
 * @todo Refactor this with PageHandler with PageMapSelectors and define reusable function
 */
export default class PsCheckout {
  /**
   * @param {object} instancePayPalSdk
   * @param {function} instancePayPalSdk.getFundingSources
   * @param {object} instancePayPalSdk.Buttons
   * @param {function} instancePayPalSdk.Buttons.isEligible
   * @param {function} instancePayPalSdk.Buttons.render
   * @param {object} instancePayPalSdk.Marks
   * @param {function} instancePayPalSdk.Marks.isEligible
   * @param {function} instancePayPalSdk.Marks.render
   * @param {object} instancePayPalSdk.HostedFields
   * @param {function} instancePayPalSdk.HostedFields.isEligible
   * @param {function} instancePayPalSdk.HostedFields.render
   * @param {object} config
   * @param {string} config.createUrl
   * @param {string} config.checkCartUrl
   * @param {string} config.validateOrderUrl
   * @param {string} config.confirmationUrl
   * @param {string} config.cancelUrl
   * @param {object} config.translations
   */
  constructor(instancePayPalSdk, config) {
    this.instancePayPalSdk = instancePayPalSdk;
    this.config = config;
  }

  loadCheckout() {
    const config = this.config;

    if (undefined === this.instancePayPalSdk) {
      throw new Error('No PayPal Javascript SDK Instance');
    }

    const buttonsContainer = document.getElementById(
      "ps_checkout-buttons-container"
    );
    const paymentOptionsContainer = document.querySelector(
      ".payment-options"
    );
    const paymentOptions = paymentOptionsContainer.querySelectorAll(
      '[name="payment-option"]'
    );
    const paymentOption = document.querySelector(
      '[data-module-name="ps_checkout"]'
    );
    const paymentOptionIdentifier =
      null !== paymentOption ? paymentOption.id : null;
    const paymentOptionContainer = document.getElementById(
      paymentOptionIdentifier + "-container"
    );
    const paymentOptionAdditionalInformation = document.getElementById(
      paymentOptionIdentifier + "-additional-information"
    );
    const paymentOptionFormContainer = document.getElementById(
      "pay-with-" + paymentOptionIdentifier + "-form"
    );
    const notificationPaymentCanceled = document.getElementById(
      "ps_checkout-canceled"
    );
    const notificationPaymentError = document.getElementById(
      "ps_checkout-error"
    );

    paymentOptionContainer.style.display = "none";

    paymentOptions.forEach(element => {
      element.addEventListener("change", () => {
        document
          .querySelectorAll(".checkout-smartbutton")
          .forEach(smartbutton => {
            notificationPaymentCanceled.style.display = "none";
            notificationPaymentError.style.display = "none";
            smartbutton.style.display = "none";
          });
      });
    });

    this.instancePayPalSdk.getFundingSources().forEach(fundingSource => {
      let mark = this.instancePayPalSdk.Marks({
        fundingSource: fundingSource
      });

      if (mark.isEligible()) {
        let fundingSourceButtonElement = document.createElement("div");
        fundingSourceButtonElement.id = "button-" + fundingSource;
        fundingSourceButtonElement.classList.add("checkout-smartbutton");
        buttonsContainer.append(fundingSourceButtonElement);
        let fundingSourceButtonContainer = buttonsContainer.querySelector(
          "#button-" + fundingSource
        );
        let fundingSourcePaymentOptionLabel =
          undefined !== config.translations[fundingSource]
            ? config.translations[fundingSource]
            : config.translations["default"];
        let fundingSourcePaymentOptionIdentifier =
          paymentOptionIdentifier + "-" + fundingSource;
        let fundingSourcePaymentOptionContainer = paymentOptionContainer.cloneNode(
          true
        );
        let fundingSourcePaymentOptionContainerLabel = fundingSourcePaymentOptionContainer.querySelector(
          'label[for="' + paymentOptionIdentifier + '"]'
        );
        let fundingSourcePaymentOptionContainerLabelElement = document.createElement(
          "label"
        );
        let fundingSourcePaymentOptionContainerLabelElementSpan = document.createElement(
          "span"
        );
        let fundingSourcePaymentOptionContainerLabelElementMark = document.createElement(
          "div"
        );
        let fundingSourcePaymentOption = fundingSourcePaymentOptionContainer.querySelector(
          '[name="payment-option"]'
        );
        let fundingSourcePaymentSelect = fundingSourcePaymentOptionContainer.querySelector(
          '[name="select_payment_option"]'
        );
        let fundingSourcePaymentOptionFormContainer = paymentOptionFormContainer.cloneNode(
          true
        );
        let fundingSourcePaymentOptionFormButton = fundingSourcePaymentOptionFormContainer.querySelector(
          "#pay-with-" + paymentOptionIdentifier
        );

        fundingSourcePaymentOptionContainer.id =
          fundingSourcePaymentOptionIdentifier + "-container";
        fundingSourcePaymentOptionContainer.style.display = "block";

        fundingSourcePaymentOptionContainerLabelElementMark.id =
          fundingSource + "-mark";
        fundingSourcePaymentOptionContainerLabelElementMark.style.display =
          "inline-block";

        fundingSourcePaymentOptionContainerLabelElementSpan.innerText = fundingSourcePaymentOptionLabel;
        fundingSourcePaymentOptionContainerLabelElementSpan.append(
          fundingSourcePaymentOptionContainerLabelElementMark
        );

        fundingSourcePaymentOptionContainerLabelElement.htmlFor = fundingSourcePaymentOptionIdentifier;
        fundingSourcePaymentOptionContainerLabelElement.append(
          fundingSourcePaymentOptionContainerLabelElementSpan
        );

        fundingSourcePaymentOptionContainerLabel.replaceWith(
          fundingSourcePaymentOptionContainerLabelElement
        );

        fundingSourcePaymentOption.id = fundingSourcePaymentOptionIdentifier;
        fundingSourcePaymentOption.addEventListener("change", () => {
          document
            .querySelectorAll(".checkout-smartbutton")
            .forEach(smartbutton => {
              notificationPaymentCanceled.style.display = "none";
              notificationPaymentError.style.display = "none";
              smartbutton.style.display = "none";
            });

          if (fundingSourcePaymentOption.checked) {
            fundingSourceButtonContainer.style.display = "block";
          }
        });

        fundingSourcePaymentSelect.value = fundingSourcePaymentOptionIdentifier;

        fundingSourcePaymentOptionFormContainer.id =
          "pay-with-" + fundingSourcePaymentOptionIdentifier + "-form";
        fundingSourcePaymentOptionFormButton.id =
          "pay-with-" + fundingSourcePaymentOptionIdentifier;

        paymentOptionsContainer.append(fundingSourcePaymentOptionContainer);
        paymentOptionsContainer.append(
          fundingSourcePaymentOptionFormContainer
        );
        fundingSourceButtonContainer.style.display = "none";

        if (
          "card" === fundingSource &&
          this.instancePayPalSdk.HostedFields &&
          this.instancePayPalSdk.HostedFields.isEligible()
        ) {
          const fundingSourcePaymentOptionAdditionalInformation = paymentOptionAdditionalInformation.cloneNode(true);
          paymentOptionAdditionalInformation.remove();
          fundingSourcePaymentOptionAdditionalInformation.id = fundingSourcePaymentOptionIdentifier + "-additional-information";
          paymentOptionsContainer.append(
            fundingSourcePaymentOptionAdditionalInformation
          );
          const hostedFieldsForm = document.getElementById(
            "ps_checkout-hosted-fields-form"
          );
          hostedFieldsForm.style.display = "block";
          const hostedFieldSubmitButton = document.querySelector("#payment-confirmation [type='submit']").cloneNode(true);
          hostedFieldSubmitButton.id = "ps_checkout-hosted-submit-button";
          hostedFieldSubmitButton.type = "button";
          hostedFieldSubmitButton.classList.remove("disabled");
          fundingSourceButtonContainer.append(hostedFieldSubmitButton);
          this.createHostedFields(hostedFieldSubmitButton.id);
        } else {
          this.createButton(fundingSource).render("#button-" + fundingSource);
        }

        mark.render("#" + fundingSource + "-mark");
      }
    });
  }

  /**
   * @returns {*}
   */
  createHostedFields(hostedFieldsSubmitButtonIdentifier) {
    const config = this.config;
    const notificationPaymentError = document.getElementById(
      "ps_checkout-error"
    );
    const notificationPaymentErrorText = document.getElementById(
      "ps_checkout-error-text"
    );

    return this.instancePayPalSdk.HostedFields.render({
      createOrder: function() {
        console.log("CreateOrder hosted fields");
        return fetch(config.createUrl, {
          method: "post",
          headers: {
            "content-type": "application/json"
          }
        })
          .then(function(response) {
            console.log("Result createOrder hosted fields");
            console.log(response);
            return response.json();
          })
          .then(function(response) {
            console.log("Data createOrder hosted fields");
            console.log(response);
            return response.body.orderID;
          });
      },
      styles: {
        input: {
          "font-size": "17px",
          "font-family": "helvetica, tahoma, calibri, sans-serif",
          color: "#3a3a3a"
        },
        ":focus": {
          color: "black"
        }
      },
      fields: {
        number: {
          selector: "#ps_checkout-hosted-fields-card-number",
          placeholder: "Card number" //@todo translations
        },
        cvv: {
          selector: "#ps_checkout-hosted-fields-card-cvv",
          placeholder: "XXX" //@todo translations
        },
        expirationDate: {
          selector: "#ps_checkout-hosted-fields-card-expiration-date",
          placeholder: "MM/YY"
        }
      }
    }).then(function(hostedFields) {
      const hostedFieldsForm = document.getElementById(
        "ps_checkout-hosted-fields-form"
      );

      if (null !== hostedFieldsForm) {
        const hostedFieldsSubmitButton = document.getElementById(hostedFieldsSubmitButtonIdentifier);
        hostedFieldsSubmitButton.addEventListener("click", event => {
          event.preventDefault();
          hostedFieldsSubmitButton.disabled = true;
          hostedFields
            .submit({
              contingencies: ["3D_SECURE"]
            })
            .then(function(payload) {
              console.log(payload);
              if (undefined === payload.liabilityShift) {
                notificationPaymentError.style.display = "block";
                notificationPaymentErrorText.textContent = "Hosted fields : Liability is undefined.";
                console.log("Hosted fields : Liability is undefined.");
              }

              if (false === payload.liabilityShift) {
                notificationPaymentError.style.display = "block";
                notificationPaymentErrorText.textContent = "Hosted fields : Liability is false.";
                console.log("Hosted fields : Liability is false.");
              }

              if ("Possible" === payload.liabilityShift) {
                notificationPaymentError.style.display = "block";
                notificationPaymentErrorText.textContent = "Hosted fields : Liability might shift to the card issuer.";
                console.log(
                  "Hosted fields : Liability might shift to the card issuer."
                );
              }

              if ("No" === payload.liabilityShift) {
                notificationPaymentError.style.display = "block";
                notificationPaymentErrorText.textContent = "Hosted fields : Liability is with the merchant.";
                console.log("Hosted fields : Liability is with the merchant.");
              }

              if ("Unknown" === payload.liabilityShift) {
                notificationPaymentError.style.display = "block";
                notificationPaymentErrorText.textContent = "Hosted fields : The authentication system is not available.";
                console.log(
                  "Hosted fields : The authentication system is not available."
                );
              }

              if (payload.liabilityShift) {
                notificationPaymentError.style.display = "block";
                notificationPaymentErrorText.textContent = "Hosted fields : Liability might shift to the card issuer.";
                console.log(
                  "Hosted fields : Liability might shift to the card issuer."
                );
              }
            })
            .catch(function(error) {
              notificationPaymentError.style.display = "block";
              notificationPaymentErrorText.textContent = error.message;
              hostedFieldsSubmitButton.disabled = false;
            });
        });
      }
    });
  }

  /**
   * @returns {*}
   */
  createButton(fundingSource) {
    const config = this.config;
    const termsAndConditionsContainer = document.getElementById(
      "conditions-to-approve"
    );
    const termsAndConditionsCheckboxes =
      null !== termsAndConditionsContainer
        ? termsAndConditionsContainer.querySelectorAll(
        'input[type="checkbox"]'
        )
        : null;
    let isTermsAndConditionsApproved = true;
    const notificationConditionsToApprove = document.querySelector(
      ".accept-cgv"
    );
    const notificationPaymentCanceled = document.getElementById(
      "ps_checkout-canceled"
    );
    const notificationPaymentError = document.getElementById(
      "ps_checkout-error"
    );
    const notificationPaymentErrorText = document.getElementById(
      "ps_checkout-error-text"
    );

    return this.instancePayPalSdk.Buttons({
      fundingSource: fundingSource,
      style: {
        label: "pay"
      },
      onInit(data, actions) {
        if (null !== termsAndConditionsCheckboxes) {
          isTermsAndConditionsApproved = false;
          actions.disable();
          termsAndConditionsCheckboxes.forEach(function(
            termsAndConditionsCheckbox
          ) {
            termsAndConditionsCheckbox.addEventListener("change", () => {
              isTermsAndConditionsApproved = true;
              termsAndConditionsCheckboxes.forEach(function(
                termsAndConditionsCheckbox
              ) {
                if (false === termsAndConditionsCheckbox.checked) {
                  isTermsAndConditionsApproved = false;
                }
              });

              if (true === isTermsAndConditionsApproved) {
                notificationConditionsToApprove.style.display = "none";
                actions.enable();
              } else {
                notificationConditionsToApprove.style.display = "block";
                actions.disable();
              }
            });
          });
        }
      },
      onClick: function(data, actions) {
        if (false === isTermsAndConditionsApproved) {
          notificationConditionsToApprove.style.display = "block";
          return;
        }

        notificationPaymentCanceled.style.display = "none";
        notificationPaymentError.style.display = "none";

        return fetch(config.checkCartUrl, {
          method: "post",
          headers: {
            "content-type": "application/json"
          },
          body: JSON.stringify(data)
        })
          .then(response => {
            if (false === response.ok) {
              throw new Error(response.statusText);
            }

            return response.json();
          })
          .then(function(data) {
            if (undefined !== data.status && "true" === data.status) {
              return actions.reject();
            } else {
              return actions.resolve();
            }
          })
          .catch(function(error) {
            notificationPaymentError.style.display = "block";
            notificationPaymentErrorText.textContent = error.message;
            actions.reject();
          });
      },
      onError: function(error) {
        notificationPaymentError.style.display = "block";
        console.log(error);
        if (error instanceof TypeError) {
          console.log("onError");
          notificationPaymentErrorText.textContent = error.message;
        }
      },
      onApprove: function(data, actions) {
        return fetch(config.validateOrderUrl, {
          method: "post",
          headers: {
            "content-type": "application/json"
          },
          body: JSON.stringify(data)
        })
          .then(response => {
            if (false === response.ok) {
              throw new Error(response.statusText);
            }

            return response.json();
          })
          .then(function(response) {
            if (
              undefined !== response.body &&
              "COMPLETED" === response.body.paypal_status
            ) {
              let confirmationUrl = new URL(config.confirmationUrl);
              confirmationUrl.searchParams.append(
                "id_cart",
                response.body.id_cart
              );
              confirmationUrl.searchParams.append(
                "id_module",
                response.body.id_module
              );
              confirmationUrl.searchParams.append(
                "id_order",
                response.body.id_order
              );
              confirmationUrl.searchParams.append(
                "key",
                response.body.secure_key
              );
              confirmationUrl.searchParams.append(
                "paypal_order",
                response.body.paypal_order
              );
              confirmationUrl.searchParams.append(
                "paypal_transaction",
                response.body.paypal_transaction
              );

              window.location.href = confirmationUrl.toString();
            }

            if (
              undefined !== response.error &&
              "INSTRUMENT_DECLINED" === response.error
            ) {
              return actions.restart();
            }
          })
          .catch(function(error) {
            notificationPaymentError.style.display = "block";
            notificationPaymentErrorText.textContent = error.message;
          });
      },
      onCancel: function(data) {
        notificationPaymentCanceled.style.display = "block";

        return fetch(config.cancelUrl, {
          method: "post",
          headers: {
            "content-type": "application/json"
          },
          body: JSON.stringify(data)
        })
          .then(response => {
            if (false === response.ok) {
              throw new Error(response.statusText);
            }
          })
          .catch(function(error) {
            notificationPaymentError.style.display = "block";
            notificationPaymentErrorText.textContent = error.message;
          });
      },
      createOrder: function(data) {
        return fetch(config.createUrl, {
          method: "post",
          headers: {
            "content-type": "application/json"
          },
          body: JSON.stringify(data)
        })
          .then(response => {
            if (false === response.ok) {
              throw new Error(response.statusText);
            }

            return response.json();
          })
          .then(data => {
            if (undefined !== data.body.orderID) {
              return data.body.orderID;
            }
          })
          .catch(error => {
            notificationPaymentError.style.display = "block";
            notificationPaymentErrorText.textContent =
              error.message + " " + error.name;
          });
      }
    });
  }
}
