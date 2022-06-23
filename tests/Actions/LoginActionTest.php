<?php

namespace Uniform\Tests\Actions;

use Uniform\Form;
use Uniform\Tests\TestCase;
use Uniform\Actions\LoginAction;
use Uniform\Exceptions\PerformerException;

class LoginActionTest extends TestCase
{
    protected $form;
    public function setUp(): void
    {
        parent::setUp();
        $this->form = new Form;
        $this->form->data('username', 'joe');
        $this->form->data('password', 'secret');
    }

    public function testWrongUser()
    {
        $action = new LoginActionStub($this->form);
        $this->expectException(PerformerException::class);
        $action->perform();
    }

    public function testWrongPassword()
    {
        $action = new LoginActionStub($this->form);
        $action->user = new UserStub(false);
        $this->expectException(PerformerException::class);
        $action->perform();
    }

    public function testSuccess()
    {
        $user = new UserStub(true);
        $action = new LoginActionStub($this->form);
        $action->user = $user;
        $action->perform();
        $this->assertEquals('joe', $action->name);
        $this->assertEquals('secret', $user->password);
    }

    public function testOptions()
    {
        $this->form->forget('username');
        $this->form->forget('password');

        $this->form->data('un', 'joe');
        $this->form->data('pw', 'secret');
        $user = new UserStub(true);
        $action = new LoginActionStub($this->form, [
            'user-field' => 'un',
            'password-field' => 'pw',
        ]);
        $action->user = $user;
        $action->perform();
        $this->assertEquals('joe', $action->name);
        $this->assertEquals('secret', $user->password);
    }
}

class LoginActionStub extends LoginAction
{
    public $user;
    protected function getUser($name)
    {
        $this->name = $name;
        return $this->user;
    }
}

class UserStub
{
    public $login;
    public $password;
    public function __construct($login)
    {
        $this->login = $login;
    }

    public function login($password)
    {
        $this->password = $password;
        return $this->login;
    }
}
