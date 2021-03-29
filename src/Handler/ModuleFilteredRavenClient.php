<?php

namespace PrestaShop\Module\PrestashopCheckout\Handler;

use PrestaShop\Module\PrestashopCheckout\Environment\SentryEnv;
use Ps_checkout;
use Raven_Client;

class ModuleFilteredRavenClient extends Raven_Client
{
    public function __construct(Ps_checkout $module, SentryEnv $sentryEnv)
    {
        parent::__construct(
            $sentryEnv->getDsn(),
            [
                'level' => 'warning',
                'tags' => [
                    'php_version' => phpversion(),
                    'ps_checkout_version' => $module->version,
                    'prestashop_version' => _PS_VERSION_,
                ],
            ]
        );
    }

    public function capture($data, $stack = null, $vars = null)
    {
        /*
            Content of $data:
            array:2 [▼
            "exception" => array:1 [▼
                "values" => array:1 [▼
                    0 => array:3 [▼
                        "value" => "Class 'DogeInPsFacebook' not found"
                        "type" => "Error"
                        "stacktrace" => array:1 [▼
                            "frames" => array:4 [▼
                                0 => array:7 [▼
                                    "filename" => "index.php"
                                    "lineno" => 93
                                    "function" => null
                                    "pre_context" => array:5 [▶]
                                    "context_line" => "    Dispatcher::getInstance()->dispatch();"
                                    "post_context" => array:2 [▶]
                                    "in_app" => false
                    1 => array:3 [▼
                        [Can be defined when a subexception is set]

        */
        if (!isset($data['exception']['values'][0]['stacktrace']['frames'])) {
            dump($data);

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
        /**
         * Content of $data:
         * array:3 [▼
        "in_app" => false
         */
        $atLeastOneFileIsInApp = false;
        foreach ($data['stacktrace']['frames'] as $frame) {
            $atLeastOneFileIsInApp = $atLeastOneFileIsInApp || ((isset($frame['in_app']) && $frame['in_app']));
        }

        return $atLeastOneFileIsInApp;
    }
}
