<?php

namespace Crm\ApiModule\Api;

use Crm\ApiModule\Response\ApiResponseInterface;
use Nette\Http\Response;

class TokenCheckHandler extends ApiHandler
{
    public function params(): array
    {
        return [];
    }

    public function handle(array $params): ApiResponseInterface
    {
        $result = [
            'status' => 'ok',
        ];

        $response = new JsonResponse($result);
        $response->setHttpCode(Response::S200_OK);
        return $response;
    }
}
