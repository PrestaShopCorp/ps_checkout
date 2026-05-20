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

if (!defined('_PS_VERSION_')) {
    exit;
}

use PsCheckout\Core\SupportAccess\Service\SupportTokenService;
use PsCheckout\Infrastructure\Controller\AbstractFrontController;
use PsCheckout\Infrastructure\Logger\LoggerFileFinder;
use PsCheckout\Infrastructure\Logger\LoggerFileReader;

/**
 * Exposes ps_checkout log files to the PrestaShop internal support tool.
 * Access is protected by a per-shop Bearer token (PS_CHECKOUT_SUPPORT_TOKEN).
 * The token is generated at first use and retrievable from the module debug panel.
 */
class Ps_CheckoutSupportLogsModuleFrontController extends AbstractFrontController
{
    /** @var bool No PS customer authentication required */
    public $auth = false;

    /** @var bool Allow guest access (token-based auth only) */
    public $guestAllowed = true;

    /**
     * @see FrontController::initContent()
     */
    public function initContent()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            $this->exitWithResponse(['httpCode' => 405, 'error' => 'Method Not Allowed']);
        }

        /** @var SupportTokenService $supportTokenService */
        $supportTokenService = $this->module->getService(SupportTokenService::class);

        $token = $this->getBearerToken();

        if (!$supportTokenService->validateToken($token)) {
            $this->exitWithResponse(['httpCode' => 401, 'error' => 'Unauthorized']);
        }

        $filename = \Tools::getValue('file');

        if ($filename) {
            $this->streamLogFile((string) $filename);
        } else {
            $this->listLogFiles();
        }
    }

    /**
     * Returns the list of available log files with their dates.
     */
    private function listLogFiles(): void
    {
        /** @var LoggerFileFinder $loggerFileFinder */
        $loggerFileFinder = $this->module->getService(LoggerFileFinder::class);

        $this->exitWithResponse([
            'httpCode' => 200,
            'files' => $loggerFileFinder->getFiles(),
        ]);
    }

    /**
     * Returns paginated lines from a specific log file.
     */
    private function streamLogFile(string $filename): void
    {
        $offset = max(0, (int) \Tools::getValue('offset', 0));
        $limit = min(500, max(1, (int) \Tools::getValue('limit', 200)));

        /** @var LoggerFileReader $loggerFileReader */
        $loggerFileReader = $this->module->getService(LoggerFileReader::class);

        try {
            $data = $loggerFileReader->read($filename, $offset, $limit);
            $this->exitWithResponse(array_merge(['httpCode' => 200], $data));
        } catch (\InvalidArgumentException $e) {
            $this->exitWithResponse(['httpCode' => 400, 'error' => $e->getMessage()]);
        } catch (\RuntimeException $e) {
            $this->exitWithResponse(['httpCode' => 404, 'error' => $e->getMessage()]);
        }
    }

    /**
     * Extracts the Bearer token from the Authorization header.
     */
    private function getBearerToken(): string
    {
        $authHeader = isset($_SERVER['HTTP_AUTHORIZATION']) ? $_SERVER['HTTP_AUTHORIZATION'] : '';

        // Apache may fold it into a redirect header
        if (empty($authHeader) && function_exists('apache_request_headers')) {
            $headers = apache_request_headers();
            $authHeader = isset($headers['Authorization']) ? $headers['Authorization'] : '';
        }

        if (preg_match('/^Bearer\s+([A-Za-z0-9+\/=\-_.~]+)$/i', $authHeader, $matches)) {
            return $matches[1];
        }

        return '';
    }
}
