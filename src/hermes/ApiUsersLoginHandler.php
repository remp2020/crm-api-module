<?php

namespace Crm\ApiModule\Hermes;

use Crm\ApiModule\Repository\UserSourceAccessesRepository;
use Crm\UsersModule\Repository\UsersRepository;
use Detection\MobileDetect;
use Tomaj\Hermes\Handler\HandlerInterface;
use Tomaj\Hermes\MessageInterface;

class ApiUsersLoginHandler implements HandlerInterface
{
    private $usersRepository;

    private $userSourceAccessesRepository;

    public function __construct(UsersRepository $usersRepository, UserSourceAccessesRepository $userSourceAccessesRepository)
    {
        $this->usersRepository = $usersRepository;
        $this->userSourceAccessesRepository = $userSourceAccessesRepository;
    }

    public function handle(MessageInterface $message)
    {
        $payload = $message->getPayload();

        $source = $this->getSource($payload['input'], $payload['user_agent']);
        $user = $this->getUser($payload['input']);
        if (!$user) {
            return true;
        }

        $this->userSourceAccessesRepository->upsert($user->id, $source, $payload['access_date']);
        return true;
    }

    private function getUser($input)
    {
        if (!isset($input['POST']['email'])) {
            return null;
        }
        return $this->usersRepository->findBy('email', $input['POST']['email']);
    }

    private function getSource($input, $userAgent)
    {
        if (isset($input['GET']['source']) && !empty($input['GET']['source'])) {
            return $input['GET']['source'];
        }

        $source = 'web';
        $detector = new MobileDetect(null, $userAgent);
        if ($detector->isMobile()) {
            $source .= '_mobile';
        } elseif ($detector->isTablet()) {
            $source .= '_tablet';
        }
        return $source;
    }
}
