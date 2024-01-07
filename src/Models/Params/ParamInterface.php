<?php

namespace Crm\ApiModule\Models\Params;

/**
 * @deprecated use \Tomaj\NetteApi\Params\ParamInterface
 */
interface ParamInterface extends \Tomaj\NetteApi\Params\ParamInterface
{
    /**
     * @deprecated use validate()->isOk()
     */
    public function isValid();
}
