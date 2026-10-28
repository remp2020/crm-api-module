<?php

namespace Crm\ApiModule\Models\Authorization;

use Nette\Security\Authorizator;

/**
 * Accepts either an admin session (CRM admin pages) or a bearer API token (server-to-server).
 */
class AdminLoggedOrBearerTokenAuthorization implements ApiAuthorizationInterface, TokenAuthorizationInterface, TokenStatsTrackableInterface
{
    private ?ApiAuthorizationInterface $authorizedBy = null;

    private ?string $errorMessage = null;

    public function __construct(
        private readonly AdminLoggedAuthorization $adminLoggedAuthorization,
        private readonly BearerTokenAuthorization $bearerTokenAuthorization,
    ) {
    }

    public function authorized($resource = Authorizator::ALL): bool
    {
        $this->authorizedBy = null;

        if ($this->adminLoggedAuthorization->authorized($resource)) {
            $this->authorizedBy = $this->adminLoggedAuthorization;
            return true;
        }

        if ($this->bearerTokenAuthorization->authorized($resource)) {
            $this->authorizedBy = $this->bearerTokenAuthorization;
            return true;
        }

        $this->errorMessage = $this->bearerTokenAuthorization->getErrorMessage();
        return false;
    }

    public function getErrorMessage(): ?string
    {
        return $this->errorMessage;
    }

    public function getAuthorizedData()
    {
        return $this->authorizedBy?->getAuthorizedData() ?? [];
    }

    public function getAuthorizedToken(): ?string
    {
        if ($this->authorizedBy instanceof TokenAuthorizationInterface) {
            return $this->authorizedBy->getAuthorizedToken();
        }
        return null;
    }
}
