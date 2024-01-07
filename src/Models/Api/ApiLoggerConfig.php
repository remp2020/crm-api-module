<?php
declare(strict_types=1);

namespace Crm\ApiModule\Models\Api;

use Crm\ApiModule\Models\LoggerEndpointIdentifier;
use Tomaj\NetteApi\EndpointIdentifier;

class ApiLoggerConfig
{
    private const PATH_MODE_BLACKLIST = 'blacklist';
    private const PATH_MODE_WHITELIST = 'whitelist';

    private array $pathConfig = [];

    private string $mode;

    /**
     * @param LoggerEndpointIdentifier[] $endpointIdentifiers
     * @return void
     */
    public function setPathBlacklist(array $endpointIdentifiers): void
    {
        $this->pathConfig = [];
        $this->mode = self::PATH_MODE_BLACKLIST;
        foreach ($endpointIdentifiers as $identifier) {
            $this->pathConfig[$identifier->getVersion()][$identifier->getPackage()][$identifier->getApiAction()] = true;
        }
    }

    public function setPathWhitelist(array $endpointIdentifiers): void
    {
        $this->pathConfig = [];
        $this->mode = self::PATH_MODE_WHITELIST;
        foreach ($endpointIdentifiers as $identifier) {
            $this->pathConfig[$identifier->getVersion()][$identifier->getPackage()][$identifier->getApiAction()] = true;
        }
    }

    public function isPathEnabled(EndpointIdentifier $endpointIdentifier): bool
    {
        if (!isset($this->mode)) {
            return true;
        }

        $defaultValueToReturn = match ($this->mode) {
            self::PATH_MODE_BLACKLIST => true,
            self::PATH_MODE_WHITELIST => false,
        };

        $matchedVersion = $this->pathConfig[$endpointIdentifier->getVersion()]
            ?? $this->pathConfig['*']
            ?? null;
        if (!$matchedVersion) {
            return $defaultValueToReturn;
        }

        $matchedPackage = $matchedVersion[$endpointIdentifier->getPackage()]
            ?? $matchedVersion['*']
            ?? null;
        if (!$matchedPackage) {
            return $defaultValueToReturn;
        }

        $matchedApiAction = $matchedPackage[$endpointIdentifier->getApiAction()]
            ?? $matchedPackage['*']
            ?? null;
        if (!$matchedApiAction) {
            return $defaultValueToReturn;
        }

        return !$defaultValueToReturn;
    }
}
