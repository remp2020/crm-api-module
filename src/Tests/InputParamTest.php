<?php

namespace Crm\ApiModule\Tests;

use Crm\ApiModule\Models\Params\InputParam;
use PHPUnit\Framework\TestCase;

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
        $inputParam = new InputParam(InputParam::TYPE_POST, 'aeadsfsf');
        $this->assertEquals('af9ihsgrghre', $inputParam->getValue());
        $this->assertFalse($inputParam->isRequired());
        $this->assertEquals('aeadsfsf', $inputParam->getKey());
        $this->assertEquals(InputParam::TYPE_POST, $inputParam->getType());
    }

    public function testRequired()
    {
        $inputParam = new InputParam(InputParam::TYPE_POST, 'missing_input', true);
        $this->assertFalse($inputParam->isValid());
        $this->assertTrue($inputParam->isRequired());
        $this->assertNull($inputParam->getValue());

        $_POST['valid_input'] = 'valid_value';
        $inputParam = new InputParam(InputParam::TYPE_POST, 'valid_input', true);
        $this->assertTrue($inputParam->isValid());
        $this->assertTrue($inputParam->isRequired());
        $this->assertSame($_POST['valid_input'], $inputParam->getValue());
    }

    public function testOptional()
    {
        $inputParam = new InputParam(InputParam::TYPE_POST, 'missing_input', false);
        $this->assertTrue($inputParam->isValid());
        $this->assertFalse($inputParam->isRequired());
        $this->assertNull($inputParam->getValue());

        $_POST['valid_input'] = 'valid_value';
        $inputParam = new InputParam(InputParam::TYPE_POST, 'valid_input', false);
        $this->assertTrue($inputParam->isValid());
        $this->assertFalse($inputParam->isRequired());
        $this->assertSame($_POST['valid_input'], $inputParam->getValue());
    }

    public function testPostType()
    {
        $_GET['valid_input'] = 'one';
        $inputParam = new InputParam(InputParam::TYPE_POST, 'valid_input', true);
        $this->assertFalse($inputParam->isValid());
        $this->assertNull($inputParam->getValue());

        $_POST['valid_input'] = 'one';
        $inputParam = new InputParam(InputParam::TYPE_POST, 'valid_input', true);
        $this->assertTrue($inputParam->isValid());
        $this->assertSame($_POST['valid_input'], $inputParam->getValue());
    }

    public function testGetType()
    {
        $_POST['valid_input'] = 'one';
        $inputParam = new InputParam(InputParam::TYPE_GET, 'valid_input', true);
        $this->assertFalse($inputParam->isValid());
        $this->assertNull($inputParam->getValue());

        $_GET['valid_input'] = 'one';
        $inputParam = new InputParam(InputParam::TYPE_GET, 'valid_input', true);
        $this->assertTrue($inputParam->isValid());
        $this->assertSame($_POST['valid_input'], $inputParam->getValue());
    }


    public function testAvailableValuesWhenValueIsRequired()
    {
        $availableValues = ['one', 'two', 'three'];
        $_POST['valid_input'] = 'two';
        $_POST['invalid_input'] = 'alpha';

        $inputParam = new InputParam(InputParam::TYPE_POST, 'valid_input', true, $availableValues);
        $this->assertTrue($inputParam->isValid());
        $this->assertSame($_POST['valid_input'], $inputParam->getValue());

        $inputParam = new InputParam(InputParam::TYPE_POST, 'invalid_input', true, $availableValues);
        $this->assertFalse($inputParam->isValid());
        // InputParam returns value also in case it's not valid
        $this->assertSame($_POST['invalid_input'], $inputParam->getValue());
    }

    public function testAvailableValuesWhenValueIsOptional()
    {
        $availableValues = ['one', 'two', 'three'];
        $_POST['valid_input'] = 'two';
        $_POST['invalid_input'] = 'alpha';

        $inputParam = new InputParam(InputParam::TYPE_POST, 'valid_input', false, $availableValues);
        $this->assertTrue($inputParam->isValid());
        $this->assertSame($_POST['valid_input'], $inputParam->getValue());

        $inputParam = new InputParam(InputParam::TYPE_POST, 'invalid_input', false, $availableValues);
        $this->assertFalse($inputParam->isValid());
        // InputParam returns value also in case it's not valid
        $this->assertSame($_POST['invalid_input'], $inputParam->getValue());

        // if optional value is missing, do not validate against available values
        $inputParam = new InputParam(InputParam::TYPE_POST, 'missing_input', false, $availableValues);
        $this->assertTrue($inputParam->isValid());
        $this->assertNull($inputParam->getValue());
    }

    public function testMultiValues()
    {
        // test multi value input
        $_POST['valid_multi_input'] = ['one', 'two'];
        $inputParam = new InputParam(InputParam::TYPE_POST, 'valid_multi_input', true, null, true);
        $this->assertTrue($inputParam->isValid());
        $this->assertIsArray($inputParam->getValue());
        $this->assertSame($_POST['valid_multi_input'], $inputParam->getValue());

        // single value input should be still possible with multi flag
        $_POST['valid_input'] = 'one';
        $inputParam = new InputParam(InputParam::TYPE_POST, 'valid_input', true, null, true);
        $this->assertTrue($inputParam->isValid());
        $this->assertIsString($inputParam->getValue());
        $this->assertSame($_POST['valid_input'], $inputParam->getValue());

        // missing required multi input should fail
        $inputParam = new InputParam(InputParam::TYPE_POST, 'invalid_multi_input', true, null, true);
        $this->assertFalse($inputParam->isValid());

        // empty required multi input should be valid; this is what server receives for "invalid_multi_input[]="
        $_POST['invalid_multi_input'] = [''];
        $inputParam = new InputParam(InputParam::TYPE_POST, 'invalid_multi_input', true, null, true);
        $this->assertTrue($inputParam->isValid());

        // empty required multi input should fail when available values are set; empty string is not among allowed values
        $_POST['invalid_multi_input'] = [''];
        $inputParam = new InputParam(InputParam::TYPE_POST, 'invalid_multi_input', true, ['foo', 'bar'], true);
        $this->assertFalse($inputParam->isValid());

        // empty required multi input should be valid when available values are set with empty string
        $_POST['invalid_multi_input'] = [''];
        $inputParam = new InputParam(InputParam::TYPE_POST, 'invalid_multi_input', true, ['foo', 'bar', ''], true);
        $this->assertTrue($inputParam->isValid());

        // if optional value is missing, do not validate against multi flag
        $inputParam = new InputParam(InputParam::TYPE_POST, 'missing_input', false, null, true);
        $this->assertTrue($inputParam->isValid());
        $this->assertNull($inputParam->getValue());
    }

    public function testMultiValuesWithAvailableValues()
    {
        $availableValues = ['one', 'two', 'three'];
        $_POST['valid_multi_input'] = ['one', 'two'];
        $_POST['invalid_multi_input'] = ['one', 'alpha'];
        $_POST['invalid_2_multi_input'] = [''];

        $inputParam = new InputParam(InputParam::TYPE_POST, 'valid_multi_input', true, $availableValues, true);
        $this->assertTrue($inputParam->isValid());
        $this->assertSame($_POST['valid_multi_input'], $inputParam->getValue());

        $inputParam = new InputParam(InputParam::TYPE_POST, 'invalid_multi_input', true, $availableValues, true);
        $this->assertFalse($inputParam->isValid());
        // InputParam returns value also in case it's not valid
        $this->assertSame($_POST['invalid_multi_input'], $inputParam->getValue());

        $inputParam = new InputParam(InputParam::TYPE_POST, 'invalid_2_multi_input', true, $availableValues, true);
        $this->assertFalse($inputParam->isValid());
        // InputParam returns value also in case it's not valid
        $this->assertSame($_POST['invalid_2_multi_input'], $inputParam->getValue());

        $inputParam = new InputParam(InputParam::TYPE_POST, 'missing_input', false, $availableValues, true);
        $this->assertTrue($inputParam->isValid());
        $this->assertNull($inputParam->getValue());
    }
}
