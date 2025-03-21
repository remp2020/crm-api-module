<?php
declare(strict_types=1);

namespace Crm\ApiModule\Models\Response;

use Tomaj\NetteApi\Response\ResponseInterface;

// TODO[crm#2337]: We could probably remove also this interface. It is not used as return in open source.
//                 ApiModules' EmptyResponse, ContentModule's ContentResponse & CoverpageModule's Response
//                 can all extend directly ResponseInterface and have internal setCode() without interface.
interface ApiResponseInterface extends ResponseInterface
{
    public function getCode(): int;

    public function setCode(int $code);
}
