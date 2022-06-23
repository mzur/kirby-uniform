<?php

namespace Uniform\Tests\Actions;

use Uniform\Form;
use Kirby\Cms\App;
use Uniform\Tests\TestCase;
use Uniform\Actions\SessionStoreAction;

class SessionStoreActionTest extends TestCase
{
    protected $form;
    public function setUp(): void
    {
        parent::setUp();
        $this->form = new Form;
    }

    public function testPerform()
    {
        $session = App::instance()->session();
        $this->form->data('message', 'my message');
        $action = new SessionStoreAction($this->form);

        $this->assertEmpty($session->get('session-store'));

        $action->perform();
        $form = $session->get('session-store');

        $this->assertInstanceOf(Form::class, $form);
        $this->assertEquals('my message', $form->data('message'));
    }

    public function testPerformWithName()
    {
        $session = App::instance()->session();
        $this->form->data('message', 'my message');
        $action = new SessionStoreAction($this->form, ['name' => 'my-session-store']);

        $this->assertEmpty($session->get('my-session-store'));

        $action->perform();
        $form = $session->get('my-session-store');

        $this->assertInstanceOf(Form::class, $form);
        $this->assertEquals('my message', $form->data('message'));
    }
}
