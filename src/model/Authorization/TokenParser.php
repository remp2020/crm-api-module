<?php

namespace Crm\ApiModule\Authorization;

class TokenParser
{
    private $errorMessage = null;

    private $token = false;

    private $isOk = false;

    private $isLoaded = false;

    private function init()
    {
        if ($this->isLoaded) {
            return;
        }

        $this->isLoaded = true;

        if (!isset($_SERVER['HTTP_AUTHORIZATION'])) {
            $this->errorMessage = 'Authorization header HTTP_Authorization is not set';
            return;
        }
        $parts = explode(' ', $_SERVER['HTTP_AUTHORIZATION']);
        if (count($parts) != 2) {
            $this->errorMessage = 'Authorization header contains invalid structure';
            return;
        }
        if (!strtolower($parts[0]) == 'bearer') {
            $this->errorMessage = 'Authorization header doesn\'t contains bearer token';
            return;
        }
        $this->isOk = true;
        $this->token = $parts[1];
    }

    public function getToken()
    {
        $this->init();
        return $this->token;
    }

    public function isOk()
    {
        $this->init();
        return $this->isOk;
    }

    public function errorMessage()
    {
        $this->init();
        return $this->errorMessage;
    }
}
