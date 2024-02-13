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

namespace PrestaShop\Module\PrestashopCheckout\PayPal\Order\DTO;

class AuthenticationResponse
{
    /**
     * @var string|null
     */
    protected $liability_shift;

    /**
     * @var ThreeDSecureAuthenticationResponse|null
     */
    protected $three_d_secure;

    /**
     * @var mixed|null
     */
    protected $authentication_flow;

    /**
     * @var mixed|null
     */
    protected $exemption_details;

    /**
     * Constructor
     *
     * @param mixed[] $data Associated array of property values initializing the model
     */
    public function __construct(array $data = null)
    {
        $this->liability_shift = isset($data['liability_shift']) ? $data['liability_shift'] : null;
        $this->three_d_secure = isset($data['three_d_secure']) ? $data['three_d_secure'] : null;
        $this->authentication_flow = isset($data['authentication_flow']) ? $data['authentication_flow'] : null;
        $this->exemption_details = isset($data['exemption_details']) ? $data['exemption_details'] : null;
    }

    /**
     * Gets liability_shift.
     *
     * @return string|null
     */
    public function getLiabilityShift()
    {
        return $this->liability_shift;
    }

    /**
     * Sets liability_shift.
     *
     * @param string|null $liability_shift
     *
     * @return $this
     */
    public function setLiabilityShift($liability_shift = null)
    {
        $this->liability_shift = $liability_shift;

        return $this;
    }

    /**
     * Gets three_d_secure.
     *
     * @return ThreeDSecureAuthenticationResponse|null
     */
    public function getThreeDSecure()
    {
        return $this->three_d_secure;
    }

    /**
     * Sets three_d_secure.
     *
     * @param ThreeDSecureAuthenticationResponse|null $three_d_secure
     *
     * @return $this
     */
    public function setThreeDSecure(ThreeDSecureAuthenticationResponse $three_d_secure = null)
    {
        $this->three_d_secure = $three_d_secure;

        return $this;
    }

    /**
     * Gets authentication_flow.
     *
     * @return mixed|null
     */
    public function getAuthenticationFlow()
    {
        return $this->authentication_flow;
    }

    /**
     * Sets authentication_flow.
     *
     * @param mixed|null $authentication_flow
     *
     * @return $this
     */
    public function setAuthenticationFlow($authentication_flow = null)
    {
        $this->authentication_flow = $authentication_flow;

        return $this;
    }

    /**
     * Gets exemption_details.
     *
     * @return mixed|null
     */
    public function getExemptionDetails()
    {
        return $this->exemption_details;
    }

    /**
     * Sets exemption_details.
     *
     * @param mixed|null $exemption_details
     *
     * @return $this
     */
    public function setExemptionDetails($exemption_details = null)
    {
        $this->exemption_details = $exemption_details;

        return $this;
    }
}
