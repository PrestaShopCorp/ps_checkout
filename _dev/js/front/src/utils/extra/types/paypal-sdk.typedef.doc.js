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
 * This file exists only for documentative purposes
 */

/**
 * @typedef PayPalCardField
 *
 * @property {function} addClass
 * @property {function} clear
 * @property {function} focus
 * @property {function} removeAttribute
 * @property {function} removeClass
 * @property {function} render
 * @property {function} setAttribute
 * @property {function} setMessage
 */

/**
 * @typedef PayPalSdk
 * @type {object}
 *
 * @property {string} version
 * @property {string[]} FUNDING
 * @property {function} getCorrelationID
 * @property {function} getFundingSources
 * @property {function} isFundingEligible
 * @property {function} rememberFunding
 * @property {object} Buttons
 * @property {function} Buttons.isEligible
 * @property {function} Buttons.render
 * @property {object} Marks
 * @property {function} Marks.isEligible
 * @property {function} Marks.render
 * @property {object} HostedFields
 * @property {object} CardFields
 * @property {PayPalCardField} CardFields.CVVField
 * @property {PayPalCardField} CardFields.ExpiryField
 * @property {PayPalCardField} CardFields.NameField
 * @property {PayPalCardField} CardFields.NumberField
 * @property {function} CardFields.getState
 * @property {function} CardFields.isEligible
 * @property {function} CardFields.submit
 * @property {function} HostedFields.isEligible
 * @property {function} HostedFields.render
 * @property {object} Messages
 * @property {function} Messages.render
 */
