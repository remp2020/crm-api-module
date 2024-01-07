<?php

namespace Crm\ApiModule\Models\Api;

use Crm\ApiModule\Models\Authorization\ApiAuthorizationInterface;
use Tomaj\NetteApi\Handlers\BaseHandler;

abstract class ApiHandler extends BaseHandler implements ApiHandlerInterface
{
    private ApiAuthorizationInterface $authorization;

    private ?string $idempotentKey = null;

    private ?string $rawPayload = null;

    public function resource(): string
    {
        return self::resourceFromClass(static::class);
    }

    public function setAuthorization(ApiAuthorizationInterface $authorization): void
    {
        $this->authorization = $authorization;
    }

    public function getAuthorization(): ApiAuthorizationInterface
    {
        return $this->authorization;
    }

    /**
     * Set mocked 'php://input' payload. Useful for testing
     * @param $content
     */
    public function setRawPayload($content)
    {
        $this->rawPayload = $content;
    }

    /**
     * Retrieve raw payload from 'php://input'
     * @return false|mixed|string
     */
    public function rawPayload()
    {
        if ($this->rawPayload) {
            $toReturn = $this->rawPayload;
            $this->rawPayload = null;
            return $toReturn;
        }
        return file_get_contents('php://input');
    }

    public static function resourceFromClass($className): string
    {
        $parts = explode('\\', $className);
        $handler = str_replace('Handler', '', array_pop($parts));
        do {
            $module = array_pop($parts);
        } while (strpos($module, 'Module') === false && $module !== null);
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
