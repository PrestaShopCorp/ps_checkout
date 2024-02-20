<?php
/**
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
 */

namespace PrestaShop\Module\PrestashopCheckout\FundingSource;

class FundingSourceCollectionBuilder
{
    /**
     * @var FundingSourceConfiguration
     */
    private $configuration;

    /**
     * @var FundingSourceEligibilityConstraint
     */
    private $eligibilityConstraint;

    /**
     * @param FundingSourceConfiguration $configuration
     * @param FundingSourceEligibilityConstraint $eligibilityConstraint
     */
    public function __construct(FundingSourceConfiguration $configuration, FundingSourceEligibilityConstraint $eligibilityConstraint)
    {
        $this->configuration = $configuration;
        $this->eligibilityConstraint = $eligibilityConstraint;
    }

    /**
     * Create the funding sources collection
     *
     * @return FundingSourceEntity[]
     */
    public function create()
    {
        // PayPal
        $paypal = new FundingSourceEntity('paypal');
        $paypal->setPosition($this->configuration->getPosition('paypal', 1));
        $paypal->setIsToggleable(false);

        // PayLater
        $paylater = new FundingSourceEntity('paylater');
        $paylater->setPosition($this->configuration->getPosition('paylater', 2));
        $paylater->setIsEnabled($this->configuration->isEnabled('paylater'));
        $paylater->setCountries($this->eligibilityConstraint->getCountries('paylater'));

        // Credit card
        $card = new FundingSourceEntity('card');
        $card->setPosition($this->configuration->getPosition('card', 3));
        $card->setIsEnabled($this->configuration->isEnabled('card'));

        // Bancontact
        $bancontact = new FundingSourceEntity('bancontact');
        $bancontact->setPosition($this->configuration->getPosition('bancontact', 4));
        $bancontact->setIsEnabled($this->configuration->isEnabled('bancontact'));
        $bancontact->setCountries($this->eligibilityConstraint->getCountries('bancontact'));

        // eps
        $eps = new FundingSourceEntity('eps');
        $eps->setPosition($this->configuration->getPosition('eps', 5));
        $eps->setIsEnabled($this->configuration->isEnabled('eps'));
        $eps->setCountries($this->eligibilityConstraint->getCountries('eps'));

        // giropay
        $giropay = new FundingSourceEntity('giropay');
        $giropay->setPosition($this->configuration->getPosition('giropay', 6));
        $giropay->setIsEnabled($this->configuration->isEnabled('giropay'));
        $giropay->setCountries($this->eligibilityConstraint->getCountries('giropay'));

        // iDEAL
        $ideal = new FundingSourceEntity('ideal');
        $ideal->setPosition($this->configuration->getPosition('ideal', 7));
        $ideal->setIsEnabled($this->configuration->isEnabled('ideal'));
        $ideal->setCountries($this->eligibilityConstraint->getCountries('ideal'));

        // MyBank
        $mybank = new FundingSourceEntity('mybank');
        $mybank->setPosition($this->configuration->getPosition('mybank', 8));
        $mybank->setIsEnabled($this->configuration->isEnabled('mybank'));
        $mybank->setCountries($this->eligibilityConstraint->getCountries('mybank'));

        // P24
        $p24 = new FundingSourceEntity('p24');
        $p24->setPosition($this->configuration->getPosition('p24', 9));
        $p24->setIsEnabled($this->configuration->isEnabled('p24'));
        $p24->setCountries($this->eligibilityConstraint->getCountries('p24'));

        // BLIK
        $blik = new FundingSourceEntity('blik');
        $blik->setPosition($this->configuration->getPosition('blik', 10));
        $blik->setIsEnabled($this->configuration->isEnabled('blik'));
        $blik->setCountries($this->eligibilityConstraint->getCountries('blik'));

        return [$paypal, $paylater, $card, $bancontact, $eps, $giropay, $ideal, $mybank, $p24, $blik];
    }
}
