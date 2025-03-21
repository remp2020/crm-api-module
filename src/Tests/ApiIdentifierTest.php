<?php

namespace Crm\ApiModule\Tests;

use Crm\ApiModule\Models\Router\ApiIdentifier;
use PHPUnit\Framework\TestCase;

class ApiIdentifierTest extends TestCase
{
    public function testSimpleUse()
    {
        $apiIdentifier = new ApiIdentifier(1, 'asfsdgsd', 'sdgdsg');
        $this->assertEquals('1', $apiIdentifier->getVersion());
        $this->assertEquals('asfsdgsd', $apiIdentifier->getCategory());
        $this->assertEquals('sdgdsg', $apiIdentifier->getApiCall());
        $this->assertEquals('/v1/asfsdgsd/sdgdsg', $apiIdentifier->getApiPath());
    }

    public function testEquals()
    {
        $apiIdentifier1 = new ApiIdentifier('1', 'asfsdgsd', 'sdgdsg');
        $apiIdentifier2 = new ApiIdentifier(1, 'asfsdgsd', 'sdgdsg');
        $apiIdentifier3 = new ApiIdentifier('1', 'asfsdgsx', 'sdgdsg');

        $this->assertTrue($apiIdentifier1->equals($apiIdentifier2));
        $this->assertFalse($apiIdentifier1->equals($apiIdentifier3));
        $this->assertFalse($apiIdentifier3->equals($apiIdentifier2));
    }
}
