<?php
declare(strict_types=1);

namespace Crm\ApiModule\Models\Response;

use Nette\Http\IRequest;
use Nette\Http\IResponse;

class EmptyResponse implements ApiResponseInterface
{
    private int $code = IResponse::S204_NoContent;

    public function setCode(int $code)
    {
        $this->code = $code;
    }

    public function getCode(): int
    {
        return $this->code;
    }

    public function send(IRequest $httpRequest, IResponse $httpResponse): void
    {
        // nothing, void, emptiness...
    }
}
