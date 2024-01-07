<?php

namespace Crm\ApiModule\Api;

use Crm\ApiModule\Models\Api\ApiHandler;
use Nette\Http\Response;
use Tomaj\NetteApi\Response\JsonApiResponse;
use Tomaj\NetteApi\Response\ResponseInterface;

class TokenCheckHandler extends ApiHandler
{
    public function params(): array
    {
        return [];
    }

    public function handle(array $params): ResponseInterface
    {
        $result = [
            'status' => 'ok',
        ];

        $response = new JsonApiResponse(Response::S200_OK, $result);
        return $response;
    }
}
