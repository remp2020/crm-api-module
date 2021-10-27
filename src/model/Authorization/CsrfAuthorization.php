<?php

namespace Crm\ApiModule\Authorization;

use Nette\Http\Session;
use Nette\Security\Authorizator;

class CsrfAuthorization implements ApiAuthorizationInterface
{
    /** @var Session  */
    private $session;

    private $errorMessage = false;

    public function __construct(Session $session)
    {
        $this->session = $session;
    }

    public function authorized($resource = Authorizator::ALL)
    {
        $section = $this->session->getSection('Nette\Forms\Controls\CsrfProtection');
        $token = $this->getToken();
        if (!$token) {
            $this->errorMessage = 'No input token';
            return false;
        }

        // WARNING: VERY CAREFULL
        // zavysle od internej implementacie Nette
        // Skopriovane z Nette\Forms\Controls\CsrfProtection::validateCsrf
        $random = substr($token, 0, 10);
        $hash = $section->token ^ $this->session->getId();
        $generatedToken = $random . base64_encode(sha1($hash . $random, true));

        if ($token !== $generatedToken) {
            $this->errorMessage = 'Invalid token';
            return false;
        }

        return true;
    }

    public function getErrorMessage()
    {
        return $this->errorMessage;
    }

    private function getToken()
    {
        if (isset($_GET['token'])) {
            return $_GET['token'];
        }
        if (isset($_POST['token'])) {
            return $_POST['token'];
        }
        return false;
    }

    public function getAuthorizedData()
    {
        return [];
    }
}
