<?php

namespace Uniform\Tests\Actions;

use S;
use Uniform\Form;
use Uniform\Tests\TestCase;
use Uniform\Actions\SessionStoreAction;

class SessionStoreActionTest extends TestCase
{
    protected $form;
    public function setUp()
    {
        parent::setUp();
        $this->form = new Form;
    }

    public function testPerform()
    {
        $this->form->data('message', 'my message');
        $action = new SessionStoreAction($this->form);

        $this->assertEmpty(S::get('session-store'));

        $action->perform();
        $form = S::get('session-store');

        $this->assertInstanceOf(Form::class, $form);
        $this->assertEquals('my message', $form->data('message'));
    }

    public function testPerformWithName()
    {
        $this->form->data('message', 'my message');
        $action = new SessionStoreAction($this->form, ['name' => 'my-session-store']);

        $this->assertEmpty(S::get('my-session-store'));

        $action->perform();
        $form = S::get('my-session-store');

        $this->assertInstanceOf(Form::class, $form);
        $this->assertEquals('my message', $form->data('message'));
    }
}
