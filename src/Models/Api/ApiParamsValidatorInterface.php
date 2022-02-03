<?php

namespace Crm\ApiModule\Api;

use Crm\ApiModule\Response\ApiResponseInterface;

interface ApiParamsValidatorInterface
{
    /**
     * validateParams should validate input parameters.
     *
     * If the parameters are valid, validator is expected to return null.
     * If the parameters are not valid, return ApiResponseInterface and set correct HTTP status code.
     */
    public function validateParams(array $params): ?ApiResponseInterface;
}
