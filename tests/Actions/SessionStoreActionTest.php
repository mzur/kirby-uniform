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
        $data = $session->get('session-store');

        $this->assertEquals(['message' => 'my message'], $data);
    }

    public function testPerformWithName()
    {
        $session = App::instance()->session();
        $this->form->data('message', 'my message');
        $action = new SessionStoreAction($this->form, ['name' => 'my-session-store']);

        $this->assertEmpty($session->get('my-session-store'));

        $action->perform();
        $data = $session->get('my-session-store');

        $this->assertEquals(['message' => 'my message'], $data);
    }
}
