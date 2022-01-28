<?php

namespace Crm\ApiModule\Params;

class ParamsProcessor extends \Tomaj\NetteApi\Params\ParamsProcessor
{
    /** @var ParamInterface[] */
    private $params;

    /**
     * @see hasError() if you need first returned error.
     */
    public function isError(): bool
    {
        return parent::isError();
    }

    /**
     * This is temporary method. isError() now returns only boolean. In case you used returned error string from isError() and you don't want to rewrite your code now, use hasError().
     *
     * @deprecated use isError() to find out if there is error and getErrors() to get errors.
     */
    public function hasError()
    {
        if (!$this->isError()) {
            return false;
        }

        $errors = $this->getErrors();
        $paramKey = key(reset($errors));
        return "Invalid value for {$paramKey}";
    }
}
