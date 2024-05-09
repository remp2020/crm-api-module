<?php

namespace Crm\ApiModule\Models\Api;

use Tomaj\NetteApi\Response\JsonApiResponse;

class JsonValidationResult
{
    public static function error(JsonApiResponse $errorResponse)
    {
        return new JsonValidationResult(null, $errorResponse);
    }

    public static function json(\stdClass $parsedObject)
    {
        return new JsonValidationResult($parsedObject, null);
    }

    private function __construct(
        private ?\stdClass $parsedObject,
        private ?JsonApiResponse $errorResponse
    ) {
    }

    public function getParsedObject(): ?\stdClass
    {
        return $this->parsedObject;
    }

    public function getParsedObjectAsArray(): ?array
    {
        if ($this->parsedObject === null) {
            return null;
        }

        return json_decode(json_encode($this->parsedObject), true);
    }

    public function getErrorResponse(): ?JsonApiResponse
    {
        return $this->errorResponse;
    }

    public function hasErrorResponse(): bool
    {
        return $this->errorResponse !== null;
    }
}
