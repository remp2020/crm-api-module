<?php

namespace Crm\ApiModule\Tests;

use Crm\ApiModule\Models\Params\InputParam;
use Crm\ApiModule\Models\Params\ParamsProcessor;
use PHPUnit\Framework\TestCase;

class ParamsProcessorTest extends TestCase
{
    public function setUp(): void
    {
        $_POST = [];
        parent::setUp();
    }

    public function testSimpleUse()
    {
        $_POST['aeadsfsf'] = 'af9ihsgrghre';
        $_POST['oij34toi'] = 'xfdg09jewrgi';
        $inputParam1 = new InputParam(InputParam::TYPE_POST, 'aeadsfsf');
        $inputParam2 = new InputParam(InputParam::TYPE_POST, 'oij34toi');
        $paramsProcessor = new ParamsProcessor([$inputParam1, $inputParam2]);
        $this->assertFalse($paramsProcessor->hasError());
        $this->assertEquals(['aeadsfsf' => 'af9ihsgrghre', 'oij34toi' => 'xfdg09jewrgi'], $paramsProcessor->getValues());
    }

    public function testGetErrorMessage()
    {
        $_POST['34ytrgrgh'] = 'asdgaerhgrdh';
        $inputParam1 = new InputParam(InputParam::TYPE_POST, 'aeadsfsf');
        $inputParam2 = new InputParam(InputParam::TYPE_POST, 'oij34toi', InputParam::REQUIRED);
        $paramsProcessor = new ParamsProcessor([$inputParam1, $inputParam2]);
        $this->assertNotFalse($paramsProcessor->hasError());
    }
}
