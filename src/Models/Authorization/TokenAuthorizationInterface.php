<?php

namespace Crm\ApiModule\Models\Authorization;

interface TokenAuthorizationInterface
{
    /**
     * Token which authorized the request; null if the request was not (or not yet) authorized
     *
     * Token might be anything such as Bearer token or token received via other means
     */
    public function getAuthorizedToken(): ?string;
}
