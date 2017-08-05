<?php

namespace Uniform\Tests;

use C as Config;
use Uniform\Form;
use Jevets\Kirby\Flash;
use Uniform\Guards\Guard;
use Uniform\Actions\Action;
use Uniform\Exceptions\Exception;
use Jevets\Kirby\Exceptions\TokenMismatchException;

class FormTest extends TestCase
{
    protected $form;

    public function setUp()
    {
        parent::setUp();
        Config::set('debug', true);
        $this->form = new FormStub;
    }

    public function testValidateCsrfException()
    {
        csrf(); // Generate a token.
        $this->setExpectedException(TokenMismatchException::class);
        $this->form->validate();
    }

    public function testValidateCsrfExceptionNoDebug()
    {
        Config::set('debug', false);
        csrf(); // Generate a token.

        try {
            $this->form->validate();
            $this->assertFalse($this->form->success());
            $this->assertFalse(true);
        } catch (Exception $e) {
            $this->assertEquals('Redirected', $e->getMessage());
        }
    }

    public function testValidateCsrfSuccess()
    {
        $_POST['csrf_token'] = csrf();
        $this->form->validate();
        $this->assertTrue($this->form->success());
    }

    public function testValidateRedirect()
    {
        $_POST['csrf_token'] = csrf();
        $_POST['email'] = '';
        $this->form = new FormStub(['email' => ['rules' => ['required']]]);
        $this->assertFalse($this->form->success());
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
        $_POST['csrf_token'] = csrf();
        try {
            $this->form->guard();
            $this->assertFalse(true);
        } catch (Exception $e) {
            $this->assertEquals('Redirected', $e->getMessage());
        }
        $_POST['website'] = '';
        $this->form = new FormStub;
        $this->form->guard();
    }

    public function testGuard()
    {
        $_POST['csrf_token'] = csrf();
        $this->form = new Form;
        $guard = new GuardStub($this->form);
        $return = $this->form->guard($guard);
        $this->assertTrue($guard->performed);
        $this->assertEquals($this->form, $return);
        $this->assertTrue($this->form->success());
    }

    public function testGuardReject()
    {
        $_POST['csrf_token'] = csrf();
        $this->form = new FormStub;
        $guard = new GuardStub2($this->form);
        try {
            $this->form->guard($guard);
            $this->assertFalse(true);
        } catch (Exception $e) {
            $this->assertEquals('Redirected', $e->getMessage());
        }
    }

    public function testGuardMagicMethod()
    {
        $_POST['csrf_token'] = csrf();
        $this->form = new FormStub2;
        $return = $this->form->honeypotGuard();
        $this->assertEquals('\Uniform\Guards\HoneypotGuard', $this->form->guard);
        $this->assertEquals([], $this->form->options);
        $this->assertEquals($this->form, $return);

        $options = ['field' => 'my_field'];
        $this->form->honeypotGuard($options);
        $this->assertEquals('\Uniform\Guards\HoneypotGuard', $this->form->guard);
        $this->assertEquals($options, $this->form->options);
    }

    public function testActionValidates()
    {
        $this->setExpectedException(TokenMismatchException::class);
        $this->form->action(Action::class);
    }

    public function testActionValidatesWithoutGuards()
    {
        $this->setExpectedException(TokenMismatchException::class);
        $this->form->withoutGuards()->action(Action::class);
    }

    public function testActionCallsGuard()
    {
        $_POST['csrf_token'] = csrf();
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
        $_POST['csrf_token'] = csrf();
        $this->form = new FormStub;
        $action = new ActionStub($this->form);
        $return = $this->form->withoutGuards()->action($action);
        $this->assertTrue($action->performed);
        $this->assertEquals($this->form, $return);
        $this->assertTrue($this->form->success());
    }

    public function testActionFail()
    {
        $_POST['csrf_token'] = csrf();
        $this->form = new FormStub;
        $action = new ActionStub2($this->form);
        try {
            $this->form->withoutGuards()->action($action);
            $this->assertFalse(true);
        } catch (Exception $e) {
            $this->assertEquals('Redirected', $e->getMessage());
        }
    }

    public function testActionMagicMethod()
    {
        $_POST['csrf_token'] = csrf();
        $this->form = new FormStub2;
        $return = $this->form->emailAction();
        $this->assertEquals('\Uniform\Actions\EmailAction', $this->form->action);
        $this->assertEquals([], $this->form->options);
        $this->assertEquals($this->form, $return);

        $options = ['to' => 'jane@example.com'];
        $this->form->emailAction($options);
        $this->assertEquals('\Uniform\Actions\EmailAction', $this->form->action);
        $this->assertEquals($options, $this->form->options);
    }

    public function testWithoutRedirectValidation()
    {
        $_POST['csrf_token'] = csrf();
        $_POST['email'] = '';
        $this->form = new FormStub(['email' => ['rules' => ['required']]]);
        $this->form->withoutRedirect()->validate();
        $this->assertFalse($this->form->success());
    }

    public function testWithoutRedirectGuard()
    {
        $_POST['csrf_token'] = csrf();
        $this->form = new FormStub;
        $action = new ActionStub($this->form);
        $this->form->withoutRedirect()
            ->guard(GuardStub2::class)
            ->action($action);

        $this->assertFalse($this->form->success());
        $this->assertFalse($action->performed);
    }

    public function testWithoutRedirectAction()
    {
        $_POST['csrf_token'] = csrf();
        $this->form = new FormStub;
        $action = new ActionStub($this->form);
        $this->form->withoutRedirect()
            ->withoutGuards()
            ->action(ActionStub2::class)
            ->action($action);

        $this->assertFalse($this->form->success());
        $this->assertFalse($action->performed);
    }

    public function testWithoutFlashing()
    {
        $this->form->addField('email');
        $this->form->data('email', 'joe@user.com');
        $this->form->withoutFlashing();
        $this->form->saveData();
        $this->form->addError('email', 'error message');
        $flash = Flash::getInstance();
        $this->assertEmpty($flash->get(Form::FLASH_KEY_DATA));
        $this->assertEmpty($flash->get(Form::FLASH_KEY_ERRORS));
    }
}

class FormStub extends Form
{
    protected function fail()
    {
        $this->success = false;

        if ($this->shouldRedirect) {
            throw new Exception('Redirected');
        } else {
            parent::fail();
        }
    }
}

class FormStub2 extends FormStub
{
    public $guard;
    public $action;
    public $options;
    public function guard($guard = \Uniform\Guards\HoneypotGuard::class, $options = [])
    {
        $this->guard = $guard;
        $this->options = $options;
        return $this;
    }

    public function action($action, $options = [])
    {
        $this->action = $action;
        $this->options = $options;
        return $this;
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
