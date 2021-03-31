<?php

namespace PrestaShop\Module\PrestashopCheckout\Handler;

use Raven_Client;

class ModuleFilteredRavenClient extends Raven_Client
{
    public function __construct($dsn, array $options = [])
    {
        parent::__construct($dsn, $options);
    }

    public function capture($data, $stack = null, $vars = null)
    {
        if (!isset($data['exception']['values'][0]['stacktrace']['frames'])) {
            return null;
        }

        $allowCapture = false;
        foreach ($data['exception']['values'] as $errorValues) {
            $allowCapture = $allowCapture || $this->isErrorInApp($errorValues);
        }

        if (!$allowCapture) {
            return null;
        }

        return parent::capture($data, $stack, $vars);
    }

    /**
     * @return bool
     */
    private function isErrorInApp(array $data)
    {
        $atLeastOneFileIsInApp = false;
        foreach ($data['stacktrace']['frames'] as $frame) {
            $atLeastOneFileIsInApp = $atLeastOneFileIsInApp || ((isset($frame['in_app']) && $frame['in_app']));
        }

        return $atLeastOneFileIsInApp;
    }
}
