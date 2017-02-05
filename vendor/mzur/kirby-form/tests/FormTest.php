<?php

namespace Jevets\Kirby\Form\Tests;

use C as Config;
use Jevets\Kirby\Form;
use Jevets\Kirby\Exceptions\TokenMismatchException;

class FormTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();
        $_POST['csrf_token'] = csrf();
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
        $form->validates();
        $this->assertEquals('&lt;value&gt;', $form->old('test'));
    }

    public function testValidates()
    {
        $form = new Form(['test' => ['rules' => ['required', 'num']]]);
        $this->assertFalse($form->validates());

        $_POST['test'] = 'abc';
        $form = new Form(['test' => ['rules' => ['required', 'num']]]);
        $this->assertFalse($form->validates());

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
        $this->assertEquals('<value>', $form->data('test'));
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
        Config::set('debug', true);
        unset($_POST['csrf_token']);
        $form = new Form;
        $this->setExpectedException(TokenMismatchException::class);
        $form->validates();
    }

    public function testValidateCsrfExceptionNoDebug()
    {
        Config::set('debug', false);
        unset($_POST['csrf_token']);
        $form = new Form;
        $this->assertFalse($form->validates());
    }

    public function testValidateCsrfSuccess()
    {
        $form = new Form;
        $_POST['csrf_token'] = csrf();
        $this->assertTrue($form->validates());
    }
}

class FormStub extends Form
{
    public function addErrorsTest($data)
    {
        $this->addErrors($data);
    }
}
