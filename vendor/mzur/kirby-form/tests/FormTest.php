<?php

namespace Jevets\Kirby\Form\Tests;

use Jevets\Kirby\Form;
use Mzur\Kirby\DefuseSession\Defuse;
use Jevets\Kirby\Exceptions\TokenMismatchException;

class FormTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $_POST['csrf_token'] = csrf();
    }

    public function tearDown(): void
    {
        unset($_POST['csrf_token']);
        unset($_SERVER['HTTP_X_CSRF']);
    }

    public function testData()
    {
        $_POST['test'] = 'value';
        $_POST['test2'] = 'value2';
        $form = new Form(['test' => []]);

        $this->assertEquals('value', $form->data('test'));
        $this->assertEmpty($form->data('test2'));

        $form->data('test2', 'value2');
        $this->assertEquals('value2', $form->data('test2'));

        $this->assertEquals(['test' => 'value', 'test2' => 'value2'], $form->data());
    }

    public function testOld()
    {
        $_POST['test'] = '<value>';
        $form = new Form(['test' => ['rules' => ['num']]]);
        $this->assertEmpty($form->old('test'));
        $this->assertEquals(null, $form->old('test', null));
        $this->assertEquals(0, $form->old('test', 0));
        $form->validates();
        $this->assertEquals('&lt;value&gt;', $form->old('test'));
    }

    public function testValidatesMissing()
    {
        $form = new Form(['test' => ['rules' => ['required', 'num']]]);
        $this->assertFalse($form->validates());
    }

    public function testValidatesFalse()
    {
        $_POST['test'] = 'abc';
        $form = new Form(['test' => ['rules' => ['required', 'num']]]);
        $this->assertFalse($form->validates());
    }

    public function testValidatesTrue()
    {
        $_POST['test'] = '123';
        $form = new Form(['test' => ['rules' => ['required', 'num']]]);
        $this->assertTrue($form->validates());
    }

    public function testErrors()
    {
        $form = new Form(['test' => [
            'rules' => ['required'],
            'message' => ['Please enter something'],
        ]]);
        $this->assertEmpty($form->errors());
        $form->validates();
        $this->assertEquals(['test' => ['Please enter something']], $form->errors());
    }

    public function testError()
    {
        $form = new Form(['test' => [
            'rules' => ['required'],
            'message' => ['Please enter something'],
        ]]);
        $this->assertEmpty($form->error('test'));
        $this->assertEmpty($form->error());
        $form->validates();
        $this->assertEquals(['Please enter something'], $form->error('test'));
        $this->assertEquals(['Please enter something'], $form->error());
    }

    public function testValidatesExpiredCsrf()
    {
        $_POST['test'] = 'abc';
        $_POST['csrf_token'] = 'wrong';
        $form = new Form(['test' => []]);
        $this->assertFalse($form->validates());
        $this->assertEquals('abc', $form->old('test'));
        $this->assertEquals(['csrf_token' => ['Your session timed out. Please submit the form again.']], $form->errors());
    }

    public function testAddErrors()
    {
        $form = new FormStub;
        $form->addErrorsTest(['email' => 'Not set']);
        $this->assertEquals(['email' => ['Not set']], $form->errors());
        $form->addErrorsTest(['email' => 'No email']);
        $this->assertEquals(['email' => ['Not set', 'No email']], $form->errors());
        $form->addErrorsTest(['email' => ['another', 'error']]);
        $this->assertEquals(['email' => ['Not set', 'No email', 'another', 'error']], $form->errors());
    }

    public function testSaveData()
    {
        $_POST['username'] = 'jane';
        $_POST['password'] = 'password';
        $form = new Form([
            'username' => ['rules' => ['min' => 5]],
            'password' => ['flash' => false]
        ]);
        $form->validates();
        $this->assertEquals('jane', $form->old('username'));
        $this->assertEmpty($form->old('password'));
    }

    public function testDecodeField()
    {
        $_POST['test'] = '&lt;value&gt;';
        $form = new Form(['test' => []]);
        $this->assertEquals('<value>', $form->data('test', '', false));
        $this->assertEquals('&lt;value&gt;', $form->data('test'));
    }

    public function testTrimWhitespace()
    {
        $_POST['test'] = " value\n2\n";
        $form = new Form(['test' => []]);
        $form->data('key', ' value');
        $this->assertEquals("value\n2", $form->data('test'));
        $this->assertEquals('value', $form->data('key'));
    }

    public function testTrimWhitespaceArray()
    {
        $_POST['test'] = [' value1', 'value2 '];
        $form = new Form(['test' => []]);
        $form->data('key', [' value1', 'value2 ']);
        $this->assertEquals(['value1', 'value2'], $form->data('test'));
        $this->assertEquals(['value1', 'value2'], $form->data('key'));
    }

    public function testTrimWhitespaceNestedArray()
    {
        $_POST['test'] = [[' value1'], ['value2 ']];
        $form = new Form(['test' => []]);
        $form->data('key', [[' value1'], ['value2 ']]);
        $this->assertEquals([['value1'], ['value2']], $form->data('test'));
        $this->assertEquals([['value1'], ['value2']], $form->data('key'));
    }

    public function testForget()
    {
        $_POST['test'] = 'value';
        $form = new Form(['test' => []]);
        $this->assertEquals('value', $form->data('test'));
        $form->forget('test');
        $this->assertEmpty($form->data('test'));
    }

    public function testMultipleInstances()
    {
        $form = new Form(['test' => [
            'rules' => ['required'],
            'message' => ['Please enter something'],
        ]]);

        $form->validates();

        $form2 = new Form(['test' => []]);
        $form3 = new Form(['test' => []], 'other_form');

        $this->assertEquals(['Please enter something'], $form2->error('test'));
        $this->assertEmpty($form3->error('test'));
    }

    public function testValidateCsrfException()
    {
        unset($_POST['csrf_token']);
        Defuse::defuse(['options' => ['debug' => true]]);
        csrf();
        $form = new Form;
        $this->expectException(TokenMismatchException::class);
        $form->validates();
    }

    public function testValidateCsrfExceptionNoDebug()
    {
        unset($_POST['csrf_token']);
        Defuse::defuse(['options' => ['debug' => false]]);
        csrf();
        $form = new Form;
        $this->assertFalse($form->validates());
    }

    public function testValidateCsrfMissing()
    {
        unset($_POST['csrf_token']);
        $form = new Form;
        $this->assertFalse($form->validates());
    }

    public function testValidateCsrfMissingDebug()
    {
        unset($_POST['csrf_token']);
        Defuse::defuse(['options' => ['debug' => true]]);
        $form = new Form;
        $this->expectException(TokenMismatchException::class);
        $form->validates();
    }

    public function testValidatePostCsrfSuccess()
    {
        unset($_POST['csrf_token']);
        $form = new Form;
        $_POST['csrf_token'] = csrf();
        $this->assertTrue($form->validates());
    }

    public function testValidateHeaderCsrfSuccess()
    {
        unset($_POST['csrf_token']);
        $form = new Form;
        $_SERVER['HTTP_X_CSRF'] = csrf();
        // Refresh the server variables in Kirby's environment object.
        kirby()->environment()->detect();
        $this->assertTrue($form->validates());
    }

    public function testFileField()
    {
        $_FILES['filefield'] = [
            'name' => 'testname',
            'type' => 'text/plain',
            'size' => 10,
            'tmp_name' => 'qwert',
        ];
        $form = new Form(['filefield' => ['rules' => ['file']]]);
        $this->assertEquals($_FILES['filefield'], $form->data('filefield'));
    }

    public function testFileFieldEmpty()
    {
        $form = new Form(['filefield' => ['rules' => ['file']]]);
        $this->assertEquals('', $form->data('filefield'));
    }

    public function testFileFieldValidateEmpty()
    {
        $_FILES['filefield'] = [
            'name' => 'testname',
            'type' => 'text/plain',
            'size' => 0,
            'tmp_name' => 'qwert',
            'error' => UPLOAD_ERR_NO_FILE,
        ];
        $form = new Form(['filefield' => ['rules' => ['file']]]);
        $this->assertTrue($form->validates());
    }

    public function testFileFieldValidateRequiredNoFile()
    {
        $_FILES['filefield'] = [
            'name' => 'testname',
            'type' => 'text/plain',
            'size' => 0,
            'tmp_name' => 'qwert',
            'error' => UPLOAD_ERR_NO_FILE,
        ];
        $form = new Form(['filefield' => ['rules' => ['required', 'file']]]);
        $this->assertFalse($form->validates());
    }

    public function testFileFieldValidateRequiredOk()
    {
        $_FILES['filefield'] = [
            'name' => 'testname',
            'type' => 'text/plain',
            'size' => 0,
            'tmp_name' => 'qwert',
            'error' => UPLOAD_ERR_OK,
        ];
        $form = new Form(['filefield' => ['rules' => ['required', 'file']]]);
        $this->assertTrue($form->validates());
    }

    public function testSetData()
    {
        $_POST['titles'] = ['abc'];
        $this->form = new Form(['titles' => []]);
        $this->assertEquals(['abc'], $this->form->data('titles'));
        $this->form->data('titles', []);
        $this->assertEquals([], $this->form->data('titles'));
    }
}

class FormStub extends Form
{
    public function addErrorsTest($data)
    {
        $this->addErrors($data);
    }
}
