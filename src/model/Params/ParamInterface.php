<?php

namespace Crm\ApiModule\Params;

interface ParamInterface
{
    public function isValid();

    public function getKey();

    public function getValue();

    public function isMulti();
}
