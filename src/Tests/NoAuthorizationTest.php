<?php

namespace Crm\ApiModule\Tests;

use Crm\ApiModule\Models\Authorization\NoAuthorization;
use PHPUnit\Framework\TestCase;

class NoAuthorizationTest extends TestCase
{
    public function testResultAlwaysTrue()
    {
        $noAuthorization = new NoAuthorization();
        $this->assertTrue($noAuthorization->authorized());
    }
}
