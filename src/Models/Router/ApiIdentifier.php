<?php

namespace Crm\ApiModule\Router;

class ApiIdentifier
{
    /** @var string  */
    private $version;

    /** @var string  */
    private $category;

    /** @var string  */
    private $apiCall;

    /**
     * @param string $version
     * @param string $category
     * @param string $apiCall
     */
    public function __construct($version, $category, $apiCall)
    {
        $this->version = '' . $version;
        $this->category = $category;
        $this->apiCall = $apiCall;
    }

    /**
     * @return string
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * @return string
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * @return string
     */
    public function getApiCall()
    {
        return $this->apiCall;
    }

    /**
     * @return string
     */
    public function getApiPath()
    {
        return "/v{$this->version}/{$this->category}/{$this->apiCall}";
    }

    /**
     * @param ApiIdentifier $otherIdentifier
     * @return bool
     */
    public function equals(ApiIdentifier $otherIdentifier)
    {
        return $this->getApiPath() === $otherIdentifier->getApiPath();
    }
}
