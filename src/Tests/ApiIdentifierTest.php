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
        $apiCategory = 'category';
        $apiCall = 'call';
        $apiIdentifier = new ApiIdentifier($apiVersion, $apiCategory, $apiCall);

        $this->assertEquals($apiVersion, $apiIdentifier->getVersion());
        $this->assertEquals($apiCategory, $apiIdentifier->getCategory());
        $this->assertEquals($apiCall, $apiIdentifier->getApiCall());
        $this->assertEquals("/v{$apiVersion}/{$apiCategory}/{$apiCall}", $apiIdentifier->getApiPath());
    }

    public function testEquals()
    {
        $apiIdentifier1 = new ApiIdentifier('1', 'category1', 'apiCall1');
        $apiIdentifier2 = new ApiIdentifier('1', 'category1', 'apiCall1');
        $apiIdentifier3 = new ApiIdentifier('1', 'category2', 'apiCall1');

        $this->assertTrue($apiIdentifier1->equals($apiIdentifier2));
        $this->assertFalse($apiIdentifier1->equals($apiIdentifier3));
        $this->assertFalse($apiIdentifier3->equals($apiIdentifier2));
    }
}
