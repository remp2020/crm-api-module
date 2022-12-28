<?php

namespace Crm\ApiModule\Router;

use Tomaj\NetteApi\EndpointIdentifier;

class ApiIdentifier extends EndpointIdentifier
{
    public function __construct(
        string $version,
        string $category,
        string $apiCall,
        string $method = 'GET'
    ) {
        parent::__construct($method, $version, $category, $apiCall);
    }

    /**
     * @deprecated use getPackage()
     */
    public function getCategory(): string
    {
        return $this->getPackage();
    }

    /**
     * @deprecated use getApiAction()
     */
    public function getApiCall(): ?string
    {
        return $this->getApiAction();
    }

    /**
     * @deprecated use getUrl()
     */
    public function getApiPath(): string
    {
        return $this->getUrl();
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
