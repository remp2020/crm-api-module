<?php
declare(strict_types=1);

namespace Crm\ApiModule\Models\Response;

use Tomaj\NetteApi\Response\ResponseInterface;

interface ApiResponseInterface extends ResponseInterface
{
    public function getCode(): int;

    public function setCode(int $code);
}
