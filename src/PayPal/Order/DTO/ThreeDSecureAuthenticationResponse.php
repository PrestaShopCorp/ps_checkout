<?php

namespace PrestaShop\Module\PrestashopCheckout\PayPal\Order\DTO;

class ThreeDSecureAuthenticationResponse
{
    /**
     * @var string|null
     */
    protected $authentication_status;

    /**
     * @var string|null
     */
    protected $enrollment_status;

    /**
     * Constructor
     *
     * @param mixed[] $data Associated array of property values initializing the model
     */
    public function __construct(array $data = null)
    {
        $this->authentication_status = isset($data['authentication_status']) ? $data['authentication_status'] : null;
        $this->enrollment_status = isset($data['enrollment_status']) ? $data['enrollment_status'] : null;
    }

    /**
     * Gets authentication_status.
     *
     * @return string|null
     */
    public function getAuthenticationStatus()
    {
        return $this->authentication_status;
    }

    /**
     * Sets authentication_status.
     *
     * @param string|null $authentication_status
     *
     * @return $this
     */
    public function setAuthenticationStatus($authentication_status = null)
    {
        $this->authentication_status = $authentication_status;

        return $this;
    }

    /**
     * Gets enrollment_status.
     *
     * @return string|null
     */
    public function getEnrollmentStatus()
    {
        return $this->enrollment_status;
    }

    /**
     * Sets enrollment_status.
     *
     * @param string|null $enrollment_status
     *
     * @return $this
     */
    public function setEnrollmentStatus($enrollment_status = null)
    {
        $this->enrollment_status = $enrollment_status;

        return $this;
    }
}
