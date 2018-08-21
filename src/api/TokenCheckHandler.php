<?php

namespace Crm\ApiModule\Api;

use Crm\ApiModule\Authorization\ApiAuthorizationInterface;
use Crm\ApplicationModule\Api\ApiHandler;
use Nette\Http\Response;

class TokenCheckHandler extends ApiHandler
{
    public function params()
    {
        return [];
    }

    public function handle(ApiAuthorizationInterface $authorization)
    {
        $result = [
            'status' => 'ok',
        ];

        $response = new JsonResponse($result);
        $response->setHttpCode(Response::S200_OK);
        return $response;
    }
}
