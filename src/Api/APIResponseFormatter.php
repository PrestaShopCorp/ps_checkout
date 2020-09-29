<?php
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

namespace PrestaShop\Module\PrestashopCheckout\Api;

use PrestaShop\Module\PrestashopCheckout\Exception\CustomerExceptionConverter;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class APIResponseFormatter
{
    /**
     * @var CustomerExceptionConverter
     */
    private $converter;

    /**
     * @param CustomerExceptionConverter $converter
     */
    public function __construct(CustomerExceptionConverter $converter)
    {
        $this->converter = $converter;
    }

    /**
     * @param \Exception $exception
     *
     * @return JsonResponse
     */
    public function sendBadRequestError(\Exception $exception)
    {
        return new JsonResponse(
            [
                'status' => false,
                'httpCode' => Response::HTTP_BAD_REQUEST,
                'body' => '',
                'exceptionCode' => $exception->getCode(),
                'exceptionMessage' => $this->converter->getCustomerMessage($exception),
            ],
            Response::HTTP_BAD_REQUEST
        );
    }

    /**
     * @param \Exception $exception
     *
     * @return JsonResponse
     */
    public function sendInternalServerError(\Exception $exception)
    {
        return new JsonResponse(
            [
                'status' => false,
                'httpCode' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'body' => '',
                'exceptionCode' => $exception->getCode(),
                'exceptionMessage' => $this->converter->getCustomerMessage($exception),
            ],
            Response::HTTP_INTERNAL_SERVER_ERROR
        );
    }

    /**
     * @param array $data
     *
     * @return JsonResponse
     */
    public function sendOkResponse($data)
    {
        return new JsonResponse(
            [
                'status' => true,
                'httpCode' => Response::HTTP_OK,
                'body' => $data,
                'exceptionCode' => null,
                'exceptionMessage' => null,
            ]
        );
    }
}
