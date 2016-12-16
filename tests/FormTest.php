<?php

namespace Uniform\Tests;

use Uniform\Form;
use Uniform\Guards\Guard;
use Uniform\Actions\Action;
use Uniform\Exceptions\Exception;
use Uniform\Exceptions\TokenMismatchException;

class FormTest extends TestCase
{
    protected $form;

    public function setUp()
    {
        parent::setUp();
        $this->form = new FormStub;
    }

    public function testAddErrors()
    {
        $this->form->addErrors(['email' => 'Not set']);
        $this->assertEquals(['email' => ['Not set']], $this->form->errors());
        $this->form->addErrors(['email' => 'No email']);
        $this->assertEquals(['email' => ['Not set', 'No email']], $this->form->errors());
    }

    public function testValidateCsrfException()
    {
        $this->setExpectedException(TokenMismatchException::class);
        $this->form->validate();
    }

    public function testValidateCsrfSuccess()
    {
        $_POST['_token'] = csrf();
        $this->form->validate();
        $this->assertTrue($this->form->success());
    }

    public function testValidateRedirect()
    {
        $_POST['_token'] = csrf();
        $_POST['email'] = '';
        $this->form = new FormStub(['email' => ['rules' => ['required']]]);
        try {
            $this->form->validate();
            $this->assertFalse(true);
        } catch (Exception $e) {
            $this->assertEquals('Redirected', $e->getMessage());
        }
        $this->assertFalse($this->form->success());
    }

    public function testGuardValidates()
    {
        $this->setExpectedException(TokenMismatchException::class);
        $this->form->guard();
    }

    public function testGuardDefault()
    {
        $_POST['_token'] = csrf();
        try {
            $this->form->guard();
            $this->assertFalse(true);
        } catch (Exception $e) {
            $this->assertEquals('Redirected', $e->getMessage());
        }
        $_POST['website'] = '';
        $this->form = new FormStub(['website' => []]);
        $this->form->guard();
    }

    public function testGuard()
    {
        $_POST['_token'] = csrf();
        $this->form = new Form;
        $guard = new GuardStub($this->form);
        $this->form->guard($guard);
        $this->assertTrue($guard->performed);
    }

    public function testGuardReject()
    {
        $_POST['_token'] = csrf();
        $this->form = new FormStub;
        $guard = new GuardStub2($this->form);
        try {
            $this->form->guard($guard);
            $this->assertFalse(true);
        } catch (Exception $e) {
            $this->assertEquals('Redirected', $e->getMessage());
        }
    }

    public function testActionValidates()
    {
        $this->setExpectedException(TokenMismatchException::class);
        $this->form->action(Action::class);
    }

    public function testActionCallsGuard()
    {
        $_POST['_token'] = csrf();
        $this->form = new FormStub;
        try {
            $this->form->action(ActionStub::class);
            $this->assertFalse(true);
        } catch (Exception $e) {
            $this->assertEquals('Redirected', $e->getMessage());
        }
    }

    public function testAction()
    {
        $_POST['_token'] = csrf();
        $this->form = new FormStub;
        $action = new ActionStub($this->form);
        $this->form->withoutGuards()->action($action);
        $this->assertTrue($action->performed);
    }

    public function testActionFail()
    {
        $_POST['_token'] = csrf();
        $this->form = new FormStub;
        $action = new ActionStub2($this->form);
        try {
            $this->form->withoutGuards()->action($action);
            $this->assertFalse(true);
        } catch (Exception $e) {
            $this->assertEquals('Redirected', $e->getMessage());
        }
    }
}

class FormStub extends Form
{
    protected function redirect()
    {
        throw new Exception('Redirected');
    }
}

class GuardStub extends Guard
{
    public $performed = false;
    public function perform()
    {
        $this->performed = true;
    }
}

class GuardStub2 extends Guard
{
    public function perform()
    {
        $this->reject();
    }
}

class ActionStub extends Action
{
    public $performed = false;
    public function perform()
    {
        $this->performed = true;
    }
}

class ActionStub2 extends Action
{
    public function perform()
    {
        $this->fail();
    }
}
