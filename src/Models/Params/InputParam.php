<?php

namespace Crm\ApiModule\Params;

use Exception;

/**
 * @deprecated use params defined in tomaj/nette-api package (eg. \Tomaj\NetteApi\Params\GetInputParam or \Tomaj\NetteApi\Params\PostInputParam)
 */
class InputParam extends \Tomaj\NetteApi\Params\InputParam implements ParamInterface
{
    public function __construct(
        string $type,
        string $key,
        bool $required = self::OPTIONAL,
        ?array $availableValues = null,
        bool $multi = false
    ) {
        parent::__construct($key);
        $this->type = $type;
        $this->key = $key;
        $this->required = $required;
        if ($availableValues) {
            $this->setAvailableValues($availableValues);
        }
        $this->multi = $multi;
    }

    /**
     * @deprecated use validate()->isOk()
     */
    public function isValid()
    {
        return $this->validate()->isOk();
    }

    public function getValue()
    {
        if ($this->type == self::TYPE_GET) {
            if (isset($_GET[$this->key])) {
                return $_GET[$this->key];
            }
            return filter_input(INPUT_GET, $this->key);
        }
        if ($this->type == self::TYPE_POST) {
            if (isset($_POST[$this->key])) {
                return $_POST[$this->key];
            }
            return filter_input(INPUT_POST, $this->key);
        }

        throw new Exception('Invalid type');
    }
}
