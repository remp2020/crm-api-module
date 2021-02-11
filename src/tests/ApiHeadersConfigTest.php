<?php

namespace Crm\ApiModule\Tests;

use Crm\ApiModule\Api\ApiHeadersConfig;
use Nette\DI\Container;
use PHPUnit\Framework\TestCase;

class ApiHeadersConfigTest extends TestCase
{
    /** @var ApiHeadersConfig */
    private $apiHeadersConfig;

    protected function setUp(): void
    {
        /** @var Container $container */
        $container = $GLOBALS['container'];
        $this->apiHeadersConfig = $container->getByType(ApiHeadersConfig::class);
    }

    public function testAllowAllOrigins()
    {
        $this->apiHeadersConfig->setAllowedOrigins('*');

        $allowed = $this->apiHeadersConfig->isOriginAllowed('http://whatever.test');
        $this->assertTrue($allowed);

        $allowed = $this->apiHeadersConfig->isOriginAllowed('test.whatever');
        $this->assertTrue($allowed);

        $allowed = $this->apiHeadersConfig->isOriginAllowed('https://whatever.test');
        $this->assertTrue($allowed);
    }

    public function testNotAllowedOrigin()
    {
        $this->apiHeadersConfig->setAllowedOrigins('whatever.test');

        $allowed = $this->apiHeadersConfig->isOriginAllowed('whatever.test');
        $this->assertTrue($allowed);
        $allowed = $this->apiHeadersConfig->isOriginAllowed('whatevernot.test');
        $this->assertFalse($allowed);
    }

    public function testMultipleAllowedOrigins()
    {
        $this->apiHeadersConfig->setAllowedOrigins('whatever.test', 'whatevernot.test');

        $allowed = $this->apiHeadersConfig->isOriginAllowed('whatever.test');
        $this->assertTrue($allowed);

        $allowed = $this->apiHeadersConfig->isOriginAllowed('whatevernot.test');
        $this->assertTrue($allowed);

        $allowed = $this->apiHeadersConfig->isOriginAllowed('notallowed.test');
        $this->assertFalse($allowed);
    }

    public function testAllowedOriginsWithWildcard()
    {
        $this->apiHeadersConfig->setAllowedOrigins('whatever.*', '*.test2.test', 'test2.test', 'https://*.whatever.test');

        $allowed = $this->apiHeadersConfig->isOriginAllowed('whatever.com');
        $this->assertTrue($allowed);

        $allowed = $this->apiHeadersConfig->isOriginAllowed('whatever.org');
        $this->assertTrue($allowed);

        $allowed = $this->apiHeadersConfig->isOriginAllowed('test2.test');
        $this->assertTrue($allowed);

        $allowed = $this->apiHeadersConfig->isOriginAllowed('test3.test');
        $this->assertFalse($allowed);

        $allowed = $this->apiHeadersConfig->isOriginAllowed('https://sub1.whatever.test');
        $this->assertTrue($allowed);

        $allowed = $this->apiHeadersConfig->isOriginAllowed('https://www.whatever.test');
        $this->assertTrue($allowed);

        $allowed = $this->apiHeadersConfig->isOriginAllowed('https://foo.bar.test2.test');
        $this->assertTrue($allowed);

        $allowed = $this->apiHeadersConfig->isOriginAllowed('https://foo.bar.test3.test');
        $this->assertFalse($allowed);
    }

    public function testAllowedHeaders()
    {
        $this->apiHeadersConfig->setAllowedOrigins('*');
        $this->apiHeadersConfig->setAllowedHeaders('Foo', 'Bar');

        $this->assertEquals("Foo, Bar", $this->apiHeadersConfig->getAllowedHeaders());
    }

    public function testAllowedHttpMethods()
    {
        $this->apiHeadersConfig->setAllowedOrigins('*');
        $this->apiHeadersConfig->setAllowedHttpMethods('POST', 'GET');

        $this->assertEquals("POST, GET", $this->apiHeadersConfig->getAllowedHttpMethods());
    }
}
