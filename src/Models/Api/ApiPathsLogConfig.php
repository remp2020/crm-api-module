<?php

namespace Crm\ApiModule\Api;

class ApiPathsLogConfig
{
    private const API_PATH_PARTS = 3;

    private array $blacklist = [];

    private array $whitelist = [];

    public function setBlacklist(array $paths): void
    {
        $this->whitelist = [];
        $this->blacklist = $paths;
    }

    public function setWhitelist(array $paths): void
    {
        $this->blacklist = [];
        $this->whitelist = $paths;
    }

    public function isPathEnabled(string $path): bool
    {
        $pathParts = $this->explodePath($path);
        if (count($pathParts) !== self::API_PATH_PARTS) {
            return true;
        }

        if (count($this->blacklist) > 0) {
            return $this->isPathEnabledByBlacklist($pathParts, $this->blacklist);
        }

        if (count($this->whitelist) > 0) {
            return $this->isPathEnabledByWhitelist($pathParts, $this->whitelist);
        }

        return true;
    }

    private function isPathEnabledByBlacklist(array $pathParts, array $blacklist): bool
    {
        foreach ($blacklist as $blacklistedPath) {
            $blacklistedPathParts = $this->explodePath($blacklistedPath);
            if (count($blacklistedPathParts) !== self::API_PATH_PARTS) {
                continue;
            }

            if (($pathParts[0] === $blacklistedPathParts[0] || $blacklistedPathParts[0] === '*') &&
                ($pathParts[1] === $blacklistedPathParts[1] || $blacklistedPathParts[1] === '*') &&
                ($pathParts[2] === $blacklistedPathParts[2] || $blacklistedPathParts[2] === '*')
            ) {
                return false;
            }
        }

        return true;
    }

    private function isPathEnabledByWhitelist(array $pathParts, array $whitelist): bool
    {
        foreach ($whitelist as $whitelistedPath) {
            $whitelistedPathParts = $this->explodePath($whitelistedPath);
            if (count($whitelistedPathParts) !== self::API_PATH_PARTS) {
                continue;
            }

            if (($pathParts[0] === $whitelistedPathParts[0] || $whitelistedPathParts[0] === '*') &&
                ($pathParts[1] === $whitelistedPathParts[1] || $whitelistedPathParts[1] === '*') &&
                ($pathParts[2] === $whitelistedPathParts[2] || $whitelistedPathParts[2] === '*')
            ) {
                return true;
            }
        }

        return false;
    }

    private function explodePath(string $path): array
    {
        return explode('/', trim($path, '/'));
    }
}
