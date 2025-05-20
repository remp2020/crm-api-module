<?php

namespace Crm\ApiModule\Models\Router;

use Tomaj\NetteApi\EndpointIdentifier;

class ApiIdentifier extends EndpointIdentifier
{
    public function __construct(
        string $version,
        string $package,
        string $apiCall,
        string $method = 'GET',
    ) {
        parent::__construct($method, $version, $package, $apiCall);
    }

    public function getUrl(): string
    {
        return "/" . parent::getUrl();
    }

    /**
     * @param ApiIdentifier $otherIdentifier
     * @return bool
     */
    public function equals(ApiIdentifier $otherIdentifier): bool
    {
        return $this->getUrl() === $otherIdentifier->getUrl();
    }
}
