<?php

namespace Crm\ApiModule\Params;

use Exception;

class InputParam implements ParamInterface
{
    const TYPE_POST = 'POST';
    const TYPE_GET  = 'GET';
    // todo - ostatne ako PUT, DELETE atd..

    const OPTIONAL = false;
    const REQUIRED = true;

    private $type;

    private $key;

    private $required;

    private $availableValues;

    private $multi;

    public function __construct($type, $key, $required = self::OPTIONAL, ?array $availableValues = null, bool $multi = false)
    {
        $this->type = $type;
        $this->key = $key;
        $this->required = $required;
        if ($availableValues !== null && !is_array($availableValues)) {
            throw new Exception("Available values must be array or null. Got [{$availableValues}]");
        }
        $this->availableValues = $availableValues;
        $this->multi = $multi;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @return boolean
     */
    public function isRequired()
    {
        return $this->required;
    }

    /**
     * @return bool
     */
    public function isMulti()
    {
        return $this->multi;
    }

    public function getAvailableValues()
    {
        return $this->availableValues;
    }

    public function isValid()
    {
        $value = $this->getValue();

        if (is_array($value) && $this->isMulti()) {
            // required input, got empty array => invalid input
            if (empty($value) && $this->isRequired()) {
                return false;
            }
            foreach ($value as $val) {
                if (!$this->validateValue($val)) {
                    return false;
                }
            }
            return true;
        }

        return $this->validateValue($value);
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

    private function validateValue($value): bool
    {
        // no support for arrays on this level of parameter's value
        if (is_array($value)) {
            return false;
        }

        if ($value === null || (is_string($value) && trim($value) === '')) {
            if ($this->isRequired()) {
                return false;
            } else {
                // optional value is missing, input is still valid
                return true;
            }
        }

        if ($this->getAvailableValues() !== null) {
            return in_array($value, $this->getAvailableValues());
        }

        return true;
    }
}
