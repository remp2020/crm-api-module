<?php

namespace Crm\ApiModule\Tests;

use Crm\ApiModule\Api\ApiHeadersConfig;
use Crm\ApplicationModule\Tests\CrmTestCase;

class ApiHeadersConfigTest extends CrmTestCase
{
    /** @var ApiHeadersConfig */
    private $apiHeadersConfig;

    protected function setUp(): void
    {
        parent::setUp();

        $this->apiHeadersConfig = $this->container->getByType(ApiHeadersConfig::class);
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

    public function testAllowedCredentials()
    {
        $this->assertFalse($this->apiHeadersConfig->hasAllowedCredentialsHeader());
        $this->apiHeadersConfig->setAllowedCredentials(true);
        $this->assertTrue($this->apiHeadersConfig->hasAllowedCredentialsHeader());
    }

    public function testAccessControlMaxAge()
    {
        $this->apiHeadersConfig->setAccessControlMaxAge(600);
        $this->assertEquals(600, $this->apiHeadersConfig->getAccessControlMaxAge());
    }
}
