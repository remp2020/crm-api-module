<?php

namespace Crm\ApiModule\Events;

use League\Event\AbstractEvent;
use Nette\Database\Table\ActiveRow;

class BeforeRemoveApiTokenEvent extends AbstractEvent implements ApiTokenEventInterface
{
    private ActiveRow $apiToken;

    public function __construct(ActiveRow $apiToken)
    {
        $this->apiToken = $apiToken;
    }

    public function getApiToken(): ActiveRow
    {
        return $this->apiToken;
    }
}
