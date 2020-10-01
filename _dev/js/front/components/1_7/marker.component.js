export class MarkerComponent {
  constructor(checkout, fundingSource, htmlElementId) {
    this.checkout = checkout;
    this.config = this.checkout.config;

    this.fundingSource = fundingSource;
    this.htmlElementId = htmlElementId;
  }

  render() {
    if (this.config.customMarker[this.fundingSource.name]) {
      this.image = document.createElement('img');
      this.image.setAttribute('alt', this.fundingSource.name);
      this.image.setAttribute(
        'src',
        this.config.customMarker[this.fundingSource.name]
      );

      document.querySelector(this.htmlElementId).append(this.image);
    } else {
      this.fundingSource.mark.render(this.htmlElementId);
    }

    return this;
  }
}
