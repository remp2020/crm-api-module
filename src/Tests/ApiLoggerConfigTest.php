<?php

namespace Crm\ApiModule\Tests;

use Crm\ApiModule\Models\Api\ApiLoggerConfig;
use Crm\ApiModule\Models\Api\LoggerEndpointIdentifier;
use Crm\ApiModule\Models\Router\ApiIdentifier;
use Crm\ApplicationModule\Tests\CrmTestCase;
use PHPUnit\Framework\Attributes\DataProvider;

class ApiLoggerConfigTest extends CrmTestCase
{
    private ApiLoggerConfig $apiLoggerConfig;

    private array $testedPaths = [
        '/v1/foo/call1',
        '/v1/foo/call2',
        '/v2/foo/call3',
        '/v1/bar/call1',
        '/v2/bar/call1',
        '/v1/bar/call3',
        '/v3/bar/call3',
    ];

    protected function setUp(): void
    {
        parent::setUp();
        $this->apiLoggerConfig = $this->inject(ApiLoggerConfig::class);
    }

    #[DataProvider('loggerConfigDataProvider')]
    public function testLoggerConfig(?array $whitelist, ?array $blacklist, array $loggedPaths)
    {
        if ($whitelist) {
            $loggerIdentifiers = [];
            foreach ($whitelist as $path) {
                $loggerIdentifiers[] = new LoggerEndpointIdentifier($path[0], $path[1], $path[2]);
            }
            $this->apiLoggerConfig->setPathWhitelist($loggerIdentifiers);
        }

        if ($blacklist) {
            $loggerIdentifiers = [];
            foreach ($blacklist as $path) {
                $loggerIdentifiers[] = new LoggerEndpointIdentifier($path[0], $path[1], $path[2]);
            }
            $this->apiLoggerConfig->setPathBlacklist($loggerIdentifiers);
        }

        foreach ($this->testedPaths as $i => $path) {
            $parts = explode('/', $path);
            $identifier = new ApiIdentifier(explode('v', $parts[1])[1], $parts[2], $parts[3]);

            if (isset($loggedPaths[$path])) {
                $this->assertTrue($this->apiLoggerConfig->isPathEnabled($identifier), "Path '$path' should have been logged.");
            } else {
                $this->assertFalse($this->apiLoggerConfig->isPathEnabled($identifier), "Path '$path' should have not been logged.");
            }
            unset($loggedPaths[$path]);
        }

        $this->assertCount(0, $loggedPaths);
    }

    public static function loggerConfigDataProvider()
    {
        return [
            'nothingConfigured' => [
                'whitelist' => null,
                'blacklist' => null,
                'loggedPaths' => [
                    '/v1/foo/call1' => true,
                    '/v1/foo/call2' => true,
                    '/v2/foo/call3' => true,
                    '/v1/bar/call1' => true,
                    '/v2/bar/call1' => true,
                    '/v1/bar/call3' => true,
                    '/v3/bar/call3' => true,
                ],
            ],

            'whitelistedVersion' => [
                'whitelist' => [
                    ['1', '*', '*'],
                    ['3', '*', '*'],
                ],
                'blacklist' => null,
                'loggedPaths' => [
                    '/v1/foo/call1' => true,
                    '/v1/foo/call2' => true,
                    '/v1/bar/call1' => true,
                    '/v1/bar/call3' => true,
                    '/v3/bar/call3' => true,
                ],
            ],

            'whitelistedPackageOrVersion' => [
                'whitelist' => [
                    ['*', 'foo', '*'],
                    ['3', '*', '*'],
                ],
                'blacklist' => null,
                'loggedPaths' => [
                    '/v1/foo/call1' => true,
                    '/v1/foo/call2' => true,
                    '/v2/foo/call3' => true,
                    '/v3/bar/call3' => true,
                ],
            ],

            'whitelistedApiAction' => [
                'whitelist' => [
                    ['*', '*', 'call2'],
                ],
                'blacklist' => null,
                'loggedPaths' => [
                    '/v1/foo/call2' => true,
                ],
            ],

            'whitelistedSpecificEndpoint' => [
                'whitelist' => [
                    ['2', 'foo', 'call3'],
                    ['3', 'bar', 'call3'],
                ],
                'blacklist' => null,
                'loggedPaths' => [
                    '/v2/foo/call3' => true,
                    '/v3/bar/call3' => true,
                ],
            ],

            'blacklistedPackageOrVersion' => [
                'whitelist' => null,
                'blacklist' => [
                    ['*', 'foo', '*'],
                    ['3', '*', '*'],
                ],
                'loggedPaths' => [
                    '/v1/bar/call1' => true,
                    '/v2/bar/call1' => true,
                    '/v1/bar/call3' => true,
                ],
            ],

            'blacklistedApiAction' => [
                'whitelist' => null,
                'blacklist' => [
                    ['*', '*', 'call2'],
                ],
                'loggedPaths' => [
                    '/v1/foo/call1' => true,
                    '/v2/foo/call3' => true,
                    '/v1/bar/call1' => true,
                    '/v2/bar/call1' => true,
                    '/v1/bar/call3' => true,
                    '/v3/bar/call3' => true,
                ],
            ],

            'blacklistedSpecificEndpoint' => [
                'whitelist' => null,
                'blacklist' => [
                    ['2', 'foo', 'call3'],
                    ['3', 'bar', 'call3'],
                ],
                'loggedPaths' => [
                    '/v1/foo/call1' => true,
                    '/v1/foo/call2' => true,
                    '/v1/bar/call1' => true,
                    '/v2/bar/call1' => true,
                    '/v1/bar/call3' => true,
                ],
            ],
        ];
    }
}
