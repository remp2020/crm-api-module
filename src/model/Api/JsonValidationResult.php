<?php

namespace Crm\ApiModule\Api;

class JsonValidationResult
{
    private $parsedObject;

    private $errorResponse;

    public static function error(JsonResponse $errorResponse)
    {
        return new JsonValidationResult(null, $errorResponse);
    }

    public static function json($parsedObject)
    {
        return new JsonValidationResult($parsedObject, null);
    }

    private function __construct($parsedObject, ?JsonResponse $errorResponse)
    {
        $this->parsedObject = $parsedObject;
        $this->errorResponse = $errorResponse;
    }

    public function getParsedObject()
    {
        return $this->parsedObject;
    }

    public function getErrorResponse(): ?JsonResponse
    {
        return $this->errorResponse;
    }

    public function hasErrorResponse(): bool
    {
        return $this->errorResponse !== null;
    }
}
