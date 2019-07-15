<?php

namespace Crm\ApiModule\Api;

abstract class ApiHandler implements ApiHandlerInterface
{
    private $idempotentKey = null;

    public function resource(): string
    {
        return self::resourceFromClass(static::class);
    }

    public static function resourceFromClass($className): string
    {
        $parts = explode('\\', $className);
        $handler = str_replace('Handler', '', array_pop($parts));
        do {
            $module = array_pop($parts);
        } while (strpos($module, 'Module') === false);
        $module = str_replace('Module', '', $module);
        return "{$module}:{$handler}";
    }

    protected function idempotentKey(): ?string
    {
        return $this->idempotentKey;
    }

    public function setIdempotentKey(string $idempotentKey): void
    {
        $this->idempotentKey = $idempotentKey;
    }
}
