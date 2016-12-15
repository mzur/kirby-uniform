<?php

namespace Uniform\Tests;

use Uniform\Form;
use Uniform\Exceptions\Exception;
use Uniform\Exceptions\TokenMismatchException;

class FormTest extends TestCase
{
    public function testAddErrors()
    {
        $form = new Form;
        $form->addErrors(['email' => 'Not set']);
        $this->assertEquals(['email' => ['Not set']], $form->errors());
        $form->addErrors(['email' => 'No email']);
        $this->assertEquals(['email' => ['Not set', 'No email']], $form->errors());
    }

    public function testValidateCsrfException()
    {
        $form = new Form;
        $this->setExpectedException(TokenMismatchException::class);
        $form->validate();
    }

    public function testValidateCsrfSuccess()
    {
        $_POST['_token'] = csrf();
        $form = new Form;
        $form->validate();
        $this->assertTrue($form->success());
    }

    public function testValidateRedirect()
    {
        $_POST['_token'] = csrf();
        $_POST['email'] = '';
        $form = new Form(['email' => ['rules' => ['required']]]);
        try {
            $form->validate();
            $this->assertFalse(true);
        } catch (Exception $e) {
            $this->assertEquals('Redirected', $e->getMessage());
        }
        $this->assertFalse($form->success());
    }

    public function testGuardValidates()
    {
        $form = new Form;
        $this->setExpectedException(TokenMismatchException::class);
        $form->guard();
    }
}
