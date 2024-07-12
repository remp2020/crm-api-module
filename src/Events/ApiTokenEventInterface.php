<?php

namespace Crm\ApiModule\Events;

use Nette\Database\Table\ActiveRow;

interface ApiTokenEventInterface
{
    public function getApiToken(): ActiveRow;
}
