<?php

namespace PrestaShop\Module\PrestashopCheckout\Controller;

use Exception;
use ModuleFrontController;

class AbstractFrontController extends ModuleFrontController
{
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
     * @param string $statusMessage
     *
     * @return void
     */
    protected function exitWithCustomStatus(array $response, $statusCode = 200, $statusMessage = '')
    {
        header("HTTP/1.0 $statusCode $statusMessage");

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
}
