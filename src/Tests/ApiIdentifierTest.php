<?php
declare(strict_types=1);

namespace Crm\ApiModule\Tests;

use Crm\ApiModule\Models\Router\ApiIdentifier;
use PHPUnit\Framework\TestCase;

class ApiIdentifierTest extends TestCase
{
    public function testSimpleUse()
    {
        $apiVersion = '1';
        $apiPackage = 'package';
        $apiCall = 'call';
        $apiIdentifier = new ApiIdentifier($apiVersion, $apiPackage, $apiCall);

        $this->assertEquals($apiVersion, $apiIdentifier->getVersion());
        $this->assertEquals($apiPackage, $apiIdentifier->getPackage());
        $this->assertEquals($apiCall, $apiIdentifier->getApiAction());
        $this->assertEquals("/v{$apiVersion}/{$apiPackage}/{$apiCall}", $apiIdentifier->getUrl());
    }

    public function testEquals()
    {
        $apiIdentifier1 = new ApiIdentifier('1', 'package1', 'apiCall1');
        $apiIdentifier2 = new ApiIdentifier('1', 'package1', 'apiCall1');
        $apiIdentifier3 = new ApiIdentifier('1', 'package2', 'apiCall1');

        $this->assertTrue($apiIdentifier1->equals($apiIdentifier2));
        $this->assertFalse($apiIdentifier1->equals($apiIdentifier3));
        $this->assertFalse($apiIdentifier3->equals($apiIdentifier2));
    }
}
