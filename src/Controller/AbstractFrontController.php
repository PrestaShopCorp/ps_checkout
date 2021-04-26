<?php

namespace PrestaShop\Module\PrestashopCheckout\Controller;

use Exception;
use ModuleFrontController;
use PrestaShop\Module\PrestashopCheckout\Exception\PsCheckoutException;
use PrestaShop\Module\PrestashopCheckout\Handler\ExceptionHandler;

class AbstractFrontController extends ModuleFrontController
{
    /**
     * @var \Ps_checkout
     */
    public $module;

    /**
     * @var ExceptionHandler
     */
    protected $sentryExceptionHandler;

    public function __construct()
    {
        parent::__construct();

        $this->sentryExceptionHandler = $this->module->getService('ps_checkout.handler.exception');
    }

    /**
     * @param Exception $exception
     *
     * @return void
     */
    protected function exitWithExceptionMessage(Exception $exception)
    {
        $response = [
            'status' => false,
            'httpCode' => 500,
            'body' => '',
            'exceptionCode' => $exception->getCode(),
            'exceptionMessage' => $exception->getMessage(),
        ];

        $this->exitWithCustomStatus($response, 500);
    }

    /**
     * @param array $response
     * @param int $statusCode
     *
     * @return void
     */
    protected function exitWithCustomStatus(array $response, $statusCode = 200)
    {
        http_response_code($statusCode);

        $this->exitWithResponse($response);
    }

    /**
     * @param array $response
     *
     * @return void
     */
    protected function exitWithResponse(array $response = [])
    {
        header('Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
        header('Content-Type: application/json;charset=utf-8');

        if (!empty($response)) {
            echo json_encode($response, JSON_UNESCAPED_SLASHES);
        }

        exit;
    }

    protected function handleExceptionSendingToSentry(Exception $exception)
    {
        $exceptionClass = get_class($exception);

        if (
            'PrestaShop\Module\PrestashopCheckout\Exception\PsCheckoutException' === $exceptionClass
            && !in_array($exception->getCode(), PsCheckoutException::EXCEPTIONS_IGNORED_BY_SENTRY)
        ) {
            $this->sentryExceptionHandler->handle($exception, false);
        }
    }
}
