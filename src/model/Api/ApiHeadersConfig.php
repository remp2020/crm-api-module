<?php

namespace Crm\ApiModule\Api;

class ApiHeadersConfig
{
    protected $allowedOrigins = [];

    protected $allowedHttpMethods = [];

    protected $allowedHeaders = [];

    public function isOriginAllowed(string $origin): bool
    {
        if (empty($this->allowedOrigins)) {
            return false;
        }
        if (count($this->allowedOrigins) === 1 && $this->allowedOrigins[0] === '*') {
            return true;
        }

        // replace star in allowed origin for match anything reg expression
        $allowedOrigins = array_map(function ($element) {
            $parts = explode('*', $element);
            return implode('.*', array_map('preg_quote', $parts, ['/']));
        }, $this->allowedOrigins);

        foreach ($allowedOrigins as $allowedOrigin) {
            if (preg_match("/^$allowedOrigin$/", $origin) === 1) {
                return true;
            }
        }

        return false;
    }

    public function setAllowedOrigins(string ...$origins): void
    {
        $this->allowedOrigins = $origins;
    }

    public function setAllowedHttpMethods(string ...$httpMethod): void
    {
        $this->allowedHttpMethods = $httpMethod;
    }

    public function getAllowedHttpMethods(): string
    {
        return implode(", ", $this->allowedHttpMethods);
    }

    public function setAllowedHeaders(string ...$headers): void
    {
        $this->allowedHeaders = $headers;
    }

    public function getAllowedHeaders(): string
    {
        return implode(", ", $this->allowedHeaders);
    }
}
