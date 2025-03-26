<?php
declare(strict_types=1);

namespace Crm\ApiModule\Tests;

use PHPUnit\Framework\TestCase;
use Tomaj\NetteApi\Params\ParamsProcessor;
use Tomaj\NetteApi\Params\PostInputParam;

class ParamsProcessorTest extends TestCase
{
    private const RNDM_KEY_1 = 'random_post_key_1';
    private const RNDM_KEY_2 = 'random_post_key_2';
    private const RNDM_KEY_3 = 'random_post_key_3';

    public function setUp(): void
    {
        $_POST = [];
        parent::setUp();
    }

    public function testSimpleUse()
    {
        $rndmString1 = 'af9ihsgrghre';
        $rndmString2 = 'xfdg09jewrgi';
        $_POST[self::RNDM_KEY_1] = $rndmString1;
        $_POST[self::RNDM_KEY_2] = $rndmString2;
        $_POST[self::RNDM_KEY_3] = 'a957kjhasdmj';
        $inputParam1 = new PostInputParam(self::RNDM_KEY_1);
        $inputParam2 = new PostInputParam(self::RNDM_KEY_2);
        $paramsProcessor = new ParamsProcessor([$inputParam1, $inputParam2]);
        $this->assertFalse($paramsProcessor->isError());
        $this->assertEquals($paramsProcessor->getValues()[self::RNDM_KEY_1], $rndmString1);
        $this->assertEquals($paramsProcessor->getValues()[self::RNDM_KEY_2], $rndmString2);
        $this->assertArrayNotHasKey(self::RNDM_KEY_3, $paramsProcessor->getValues());
    }

    public function testGetErrorMessage()
    {
        $_POST[self::RNDM_KEY_1] = 'asdgaerhgrdh';
        $inputParam1 = new PostInputParam(self::RNDM_KEY_2);
        $inputParam2 = (new PostInputParam(self::RNDM_KEY_3))->setRequired();
        $paramsProcessor = new ParamsProcessor([$inputParam1, $inputParam2]);
        $this->assertTrue($paramsProcessor->isError());
        $this->assertArrayNotHasKey(self::RNDM_KEY_1, $paramsProcessor->getValues());
        $this->assertNull($paramsProcessor->getValues()[self::RNDM_KEY_2]);
        $this->assertNull($paramsProcessor->getValues()[self::RNDM_KEY_3]);
    }
}
