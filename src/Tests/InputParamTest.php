<?php
declare(strict_types=1);

namespace Crm\ApiModule\Tests;

use PHPUnit\Framework\TestCase;
use Tomaj\NetteApi\Params\GetInputParam;
use Tomaj\NetteApi\Params\InputParam;
use Tomaj\NetteApi\Params\PostInputParam;

class InputParamTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $_POST = [];
        $_GET = [];
    }

    public function testSimpleUse()
    {
        $_POST['aeadsfsf'] = 'af9ihsgrghre';
        $inputParam = new PostInputParam('aeadsfsf');
        $this->assertEquals('af9ihsgrghre', $inputParam->getValue());
        $this->assertFalse($inputParam->isRequired());
        $this->assertEquals('aeadsfsf', $inputParam->getKey());
        $this->assertEquals(InputParam::TYPE_POST, $inputParam->getType());
    }

    public function testRequired()
    {
        $inputParam = (new PostInputParam('missing_input'))->setRequired();
        $this->assertFalse($inputParam->validate()->isOk());
        $this->assertTrue($inputParam->isRequired());
        $this->assertNull($inputParam->getValue());

        $_POST['valid_input'] = 'valid_value';
        $inputParam = (new PostInputParam('valid_input'))->setRequired();
        $this->assertTrue($inputParam->validate()->isOk());
        $this->assertTrue($inputParam->isRequired());
        $this->assertSame($_POST['valid_input'], $inputParam->getValue());
    }

    public function testOptional()
    {
        $inputParam = new PostInputParam('missing_input');
        $this->assertTrue($inputParam->validate()->isOk());
        $this->assertFalse($inputParam->isRequired());
        $this->assertNull($inputParam->getValue());

        $_POST['valid_input'] = 'valid_value';
        $inputParam = new PostInputParam('valid_input');
        $this->assertTrue($inputParam->validate()->isOk());
        $this->assertFalse($inputParam->isRequired());
        $this->assertSame($_POST['valid_input'], $inputParam->getValue());
    }

    public function testPostType()
    {
        $_GET['valid_input'] = 'one';
        $inputParam = (new PostInputParam('valid_input'))->setRequired();
        $this->assertFalse($inputParam->validate()->isOk());
        $this->assertNull($inputParam->getValue());

        $_POST['valid_input'] = 'one';
        $inputParam = (new PostInputParam('valid_input'))->setRequired();
        $this->assertTrue($inputParam->validate()->isOk());
        $this->assertSame($_POST['valid_input'], $inputParam->getValue());
    }

    public function testGetType()
    {
        $_POST['valid_input'] = 'one';
        $inputParam = (new GetInputParam('valid_input'))->setRequired();
        $this->assertFalse($inputParam->validate()->isOk());
        $this->assertNull($inputParam->getValue());

        $_GET['valid_input'] = 'one';
        $inputParam = (new GetInputParam('valid_input'))->setRequired();
        $this->assertTrue($inputParam->validate()->isOk());
        $this->assertSame($_POST['valid_input'], $inputParam->getValue());
    }


    public function testAvailableValuesWhenValueIsRequired()
    {
        $availableValues = ['one', 'two', 'three'];
        $_POST['valid_input'] = 'two';
        $_POST['invalid_input'] = 'alpha';

        $inputParam = (new PostInputParam('valid_input'))->setRequired()->setAvailableValues($availableValues);
        $this->assertTrue($inputParam->validate()->isOk());
        $this->assertSame($_POST['valid_input'], $inputParam->getValue());

        $inputParam = (new PostInputParam('invalid_input'))->setRequired()->setAvailableValues($availableValues);
        $this->assertFalse($inputParam->validate()->isOk());
        // InputParam returns value also in case it's not valid
        $this->assertSame($_POST['invalid_input'], $inputParam->getValue());
    }

    public function testAvailableValuesWhenValueIsOptional()
    {
        $availableValues = ['one', 'two', 'three'];
        $_POST['valid_input'] = 'two';
        $_POST['invalid_input'] = 'alpha';

        $inputParam = (new PostInputParam('valid_input'))->setAvailableValues($availableValues);
        $this->assertTrue($inputParam->validate()->isOk());
        $this->assertSame($_POST['valid_input'], $inputParam->getValue());

        $inputParam = (new PostInputParam('invalid_input'))->setAvailableValues($availableValues);
        $this->assertFalse($inputParam->validate()->isOk());
        // InputParam returns value also in case it's not valid
        $this->assertSame($_POST['invalid_input'], $inputParam->getValue());

        // if optional value is missing, do not validate against available values
        $inputParam = (new PostInputParam('missing_input'))->setAvailableValues($availableValues);
        $this->assertTrue($inputParam->validate()->isOk());
        $this->assertNull($inputParam->getValue());
    }

    public function testMultiValues()
    {
        // test multi value input
        $_POST['valid_multi_input'] = ['one', 'two'];
        $inputParam = (new PostInputParam('valid_multi_input'))->setRequired()->setMulti();
        $this->assertTrue($inputParam->validate()->isOk());
        $this->assertIsArray($inputParam->getValue());
        $this->assertSame($_POST['valid_multi_input'], $inputParam->getValue());

        // single value input should be still possible with multi flag
        $_POST['valid_input'] = 'one';
        $inputParam = (new PostInputParam('valid_input'))->setRequired()->setMulti();
        $this->assertTrue($inputParam->validate()->isOk());
        $this->assertIsString($inputParam->getValue());
        $this->assertSame($_POST['valid_input'], $inputParam->getValue());

        // missing required multi input should fail
        $inputParam = (new PostInputParam('invalid_multi_input'))->setRequired()->setMulti();
        $this->assertFalse($inputParam->validate()->isOk());

        // empty required multi input should be valid; this is what server receives for "invalid_multi_input[]="
        $_POST['invalid_multi_input'] = [''];
        $inputParam = (new PostInputParam('invalid_multi_input'))->setRequired()->setMulti();
        $this->assertTrue($inputParam->validate()->isOk());

        // empty required multi input should fail when available values are set; empty string is not among allowed values
        $_POST['invalid_multi_input'] = [''];
        $inputParam = (new PostInputParam('invalid_multi_input'))->setRequired()->setAvailableValues(['foo', 'bar'])->setMulti();
        $this->assertFalse($inputParam->validate()->isOk());

        // empty required multi input should be valid when available values are set with empty string
        $_POST['invalid_multi_input'] = [''];
        $inputParam = (new PostInputParam('invalid_multi_input'))->setRequired()->setAvailableValues(['foo', 'bar', ''])->setMulti();
        $this->assertTrue($inputParam->validate()->isOk());

        // if optional value is missing, do not validate against multi flag
        $inputParam = (new PostInputParam('missing_input'))->setMulti();
        $this->assertTrue($inputParam->validate()->isOk());
        $this->assertNull($inputParam->getValue());
    }

    public function testMultiValuesWithAvailableValues()
    {
        $availableValues = ['one', 'two', 'three'];
        $_POST['valid_multi_input'] = ['one', 'two'];
        $_POST['invalid_multi_input'] = ['one', 'alpha'];
        $_POST['invalid_2_multi_input'] = [''];

        $inputParam = (new PostInputParam('valid_multi_input'))->setRequired()->setAvailableValues($availableValues)->setMulti();
        $this->assertTrue($inputParam->validate()->isOk());
        $this->assertSame($_POST['valid_multi_input'], $inputParam->getValue());

        $inputParam = (new PostInputParam('invalid_multi_input'))->setRequired()->setAvailableValues($availableValues)->setMulti();
        $this->assertFalse($inputParam->validate()->isOk());
        // InputParam returns value also in case it's not valid
        $this->assertSame($_POST['invalid_multi_input'], $inputParam->getValue());

        $inputParam = (new PostInputParam('invalid_2_multi_input'))->setRequired()->setAvailableValues($availableValues)->setMulti();
        $this->assertFalse($inputParam->validate()->isOk());
        // InputParam returns value also in case it's not valid
        $this->assertSame($_POST['invalid_2_multi_input'], $inputParam->getValue());

        $inputParam = (new PostInputParam('missing_input'))->setAvailableValues($availableValues)->setMulti();
        $this->assertTrue($inputParam->validate()->isOk());
        $this->assertNull($inputParam->getValue());
    }
}
