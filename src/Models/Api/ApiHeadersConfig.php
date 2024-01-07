<?php

namespace Crm\ApiModule\Models\Api;

class ApiHeadersConfig
{
    protected array $allowedOrigins = [];

    protected array $allowedHttpMethods = [];

    protected array $allowedHeaders = [];

    protected bool $allowedCredentials = false;

    protected ?int $accessControlMaxAge = null;

    public function isOriginAllowed(?string $origin): bool
    {
        if ($origin === null) {
            return true;
        }
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

    public function setAllowedCredentials(bool $allowedCredentials)
    {
        $this->allowedCredentials = $allowedCredentials;
    }

    public function hasAllowedCredentialsHeader(): bool
    {
        return $this->allowedCredentials;
    }

    public function setAccessControlMaxAge(int $seconds): void
    {
        $this->accessControlMaxAge = $seconds;
    }

    /**
     * @return int|null Max age in seconds.
     */
    public function getAccessControlMaxAge(): ?int
    {
        return $this->accessControlMaxAge;
    }
}
