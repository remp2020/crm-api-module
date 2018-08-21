<?php

namespace Crm\ApiModule\Authorization;

use Crm\ApiModule\Repository\ApiAccessRepository;
use Crm\ApiModule\Repository\ApiTokensRepository;
use Crm\ApplicationModule\Request;
use Nette\Security\IAuthorizator;

class BearerTokenAuthorization implements ApiAuthorizationInterface
{
    private $apiTokenRepository;

    private $apiAccessRepository;

    private $permissions;

    private $errorMessage = false;

    private $authorizedData = [];

    public function __construct(
        ApiTokensRepository $apiTokenRepository,
        ApiAccessRepository $apiAccessRepository,
        Permissions $permissions
    ) {
        $this->apiTokenRepository = $apiTokenRepository;
        $this->apiAccessRepository = $apiAccessRepository;
        $this->permissions = $permissions;
    }

    public function authorized($resource = IAuthorizator::ALL)
    {
        $tokenParser = new TokenParser();
        if (!$tokenParser->isOk()) {
            $this->errorMessage = $tokenParser->errorMessage();
            return false;
        }

        $token = $this->apiTokenRepository->findToken($tokenParser->getToken());
        if (!$token) {
            $this->errorMessage = 'Token doesn\'t exists';
            return false;
        }

        if (!$token->active) {
            $this->errorMessage = 'Token isn\'t active';
            return false;
        }

        if (!$this->isValidIp($token->ip_restrictions)) {
            $this->errorMessage = 'Invalid IP';
            return false;
        }

        if (!$this->permissions->allowed($token, $resource)) {
            $this->errorMessage = "Token has no access to resource [{$resource}]";
            return false;
        }

        $this->authorizedData['token'] = $token;
        return true;
    }

    public function resource($presenterClass)
    {
        $parts = explode('\\', $presenterClass);
        $handler = str_replace('Handler', '', array_pop($parts));
        do {
            $module = array_pop($parts);
        } while (strpos($module, 'Module') === false);
        $module = str_replace('Module', '', $module);
        return "{$module}:{$handler}";
    }

    private function isValidIp($ipRestrictions)
    {
        if ($ipRestrictions == '*') {
            return true;
        }
        $ip = Request::getIp();

        $ipWhiteList = str_replace([',', ' ', "\n"], '#', $ipRestrictions);
        $ipWhiteList = explode('#', $ipWhiteList);
        foreach ($ipWhiteList as $whiteIp) {
            if ($whiteIp == $ip) {
                return true;
            }
            if (strpos($whiteIp, '/') !== false) {
                return $this->ipInRange($ip, $whiteIp);
            }
        }

        return false;
    }

    private function ipInRange($ip, $range)
    {
        if (strpos($range, '/') == false) {
            $range .= '/32';
        }
        // $range is in IP/CIDR format eg 127.0.0.1/24
        list($range, $netmask) = explode('/', $range, 2);
        $range_decimal = ip2long($range);
        $ip_decimal = ip2long($ip);
        $wildcard_decimal = pow(2, (32 - $netmask)) - 1;
        $netmask_decimal = ~ $wildcard_decimal;
        return (($ip_decimal & $netmask_decimal) == ($range_decimal & $netmask_decimal));
    }

    public function getErrorMessage()
    {
        return $this->errorMessage;
    }

    public function getAuthorizedData()
    {
        return $this->authorizedData;
    }
}
