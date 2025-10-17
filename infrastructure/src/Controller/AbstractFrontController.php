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

namespace PsCheckout\Infrastructure\Controller;

use Connection;
use Exception;

class AbstractFrontController extends \ModuleFrontController
{
    /**
     * Override checkAccess to block access for bots and invalid token
     *
     * @see FrontController::checkAccess()
     */
    public function checkAccess()
    {
        return !$this->isBot() && !($this->context->customer->isLogged() && !$this->isTokenValid());
    }

    /**
     * Override initCursedPage to return json response with 403 code on POST request
     *
     * @see FrontController::initCursedPage()
     */
    public function initCursedPage()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->exitWithResponse([
                'httpCode' => 403,
                'body' => 'Forbidden',
            ]);
        }

        parent::initCursedPage();
    }

    /**
     * @param array $response
     *
     * @return void
     */
    protected function exitWithResponse(array $response = [])
    {
        ob_end_clean();
        header('Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
        header('Content-Type: application/json;charset=utf-8');
        header('X-Robots-Tag: noindex, nofollow');

        if (isset($response['httpCode'])) {
            http_response_code($response['httpCode']);
        }

        if (!empty($response)) {
            echo json_encode($response, JSON_UNESCAPED_SLASHES);
        }

        exit;
    }

    /**
     * Check if the current visitor is a bot, available since PrestaShop 8.0.0
     *
     * @return bool
     */
    public function isBot()
    {
        return method_exists(Connection::class, 'isBot') && Connection::isBot();
    }

    /**
     * @param Exception $exception
     *
     * @return void
     */
    protected function exitWithExceptionMessage(Exception $exception)
    {
        $this->exitWithResponse([
            'status' => false,
            'httpCode' => 500,
            'body' => [
                'error' => [
                    'code' => $exception->getCode(),
                    'message' => $exception->getMessage(),
                ],
            ],
        ]);
    }
}
