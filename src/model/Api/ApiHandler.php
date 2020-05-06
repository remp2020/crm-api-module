<?php

namespace Crm\ApiModule\Api;

use Crm\ApiModule\Authorization\NoAuthorization;

abstract class ApiHandler implements ApiHandlerInterface
{
    private $idempotentKey = null;

    private $mockedRawPayloadsQueue = [];

    public function resource(): string
    {
        return self::resourceFromClass(static::class);
    }

    /**
     * Push mocked 'php://input' payload into payload queue. Useful for testing
     * @param $content
     */
    public function mockRawPayload($content)
    {
        $this->mockedRawPayloadsQueue[] = $content;
    }

    /**
     * Retrieve raw payload from 'php://input' (or mocked content in case of tests)
     * @return false|mixed|string
     */
    public function rawPayload()
    {
        if ($this->mockedRawPayloadsQueue) {
            return array_shift($this->mockedRawPayloadsQueue);
        }
        return file_get_contents('php://input');
    }

    /**
     * Handle to call in tests
     */
    public function handleInTest()
    {
        return $this->handle(new NoAuthorization());
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
