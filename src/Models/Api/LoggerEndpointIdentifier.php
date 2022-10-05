<?php

namespace Crm\ApiModule\Models;

class LoggerEndpointIdentifier
{
    public function __construct(
        private string $version,
        private string $package,
        private string $apiAction,
    ) {
    }

    public function getVersion(): string
    {
        return $this->version;
    }

    public function getPackage(): string
    {
        return $this->package;
    }

    public function getApiAction(): string
    {
        return $this->apiAction;
    }
}
