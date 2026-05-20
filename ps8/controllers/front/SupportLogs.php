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

    const LOG_DIR = _PS_ROOT_DIR_ . '/var/logs/';

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
     * Returns the list of available log files for this shop.
     * Lists files directly from var/logs to avoid LoggerFileFinder date-parsing issues.
     */
    private function listLogFiles(): void
    {
        $shopId = (int) \Context::getContext()->shop->id;
        $prefix = 'ps_checkout-' . $shopId . '-';
        $files = [];

        if (is_readable(self::LOG_DIR)) {
            foreach (scandir(self::LOG_DIR, SCANDIR_SORT_DESCENDING) as $name) {
                if (strpos($name, $prefix) !== 0) {
                    continue;
                }
                $path = self::LOG_DIR . $name;
                $mtime = filemtime($path);
                $files[$name] = $mtime ? date('Y-m-d H:i', $mtime) : '';
            }
        }

        $this->exitWithResponse(['httpCode' => 200, 'files' => $files]);
    }

    /**
     * Returns paginated lines from a specific log file.
     * Validates the filename against the shop prefix to prevent path traversal.
     */
    private function streamLogFile(string $filename): void
    {
        $shopId = (int) \Context::getContext()->shop->id;
        $prefix = 'ps_checkout-' . $shopId . '-';

        // Security: only serve files belonging to this shop, no path traversal
        if (
            strpos($filename, $prefix) !== 0
            || strpos($filename, '/') !== false
            || strpos($filename, '\\') !== false
            || strpos($filename, '..') !== false
        ) {
            $this->exitWithResponse(['httpCode' => 400, 'error' => 'Invalid filename']);
        }

        $path = self::LOG_DIR . $filename;

        if (!is_file($path) || !is_readable($path)) {
            $this->exitWithResponse(['httpCode' => 404, 'error' => 'File not found']);
        }

        $offset = max(0, (int) \Tools::getValue('offset', 0));
        $limit = min(500, max(1, (int) \Tools::getValue('limit', 200)));

        $fileObj = new \SplFileObject($path);
        $lines = [];
        $lineNum = 0;

        while ($fileObj->valid()) {
            $line = $fileObj->fgets();
            if ($lineNum < $offset) {
                ++$lineNum;
                continue;
            }
            if (count($lines) >= $limit) {
                break;
            }
            if ($line !== false && $line !== '') {
                $lines[] = rtrim($line, "\r\n");
                ++$lineNum;
            }
        }

        $this->exitWithResponse([
            'httpCode' => 200,
            'filename' => $filename,
            'offset' => $offset,
            'limit' => $limit,
            'currentOffset' => $offset + count($lines),
            'eof' => !$fileObj->valid(),
            'lines' => $lines,
        ]);
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
