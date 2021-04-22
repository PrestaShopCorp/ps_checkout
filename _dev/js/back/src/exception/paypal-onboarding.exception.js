export class PaypalAccountFalseOnboardingException extends Error {
  constructor() {
    super("PayPal account 'false'");
    this.name = 'PaypalAccountFalseOnboardingException';
  }
}
