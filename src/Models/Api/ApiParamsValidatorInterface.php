<?php

namespace Crm\ApiModule\Api;

use Tomaj\NetteApi\Response\ResponseInterface;

interface ApiParamsValidatorInterface
{
    /**
     * validateParams should validate input parameters.
     *
     * If the parameters are valid, validator is expected to return null.
     * If the parameters are not valid, return ResponseInterface and set correct HTTP status code.
     */
    public function validateParams(array $params): ?ResponseInterface;
}
