<?php

namespace Uniform\Tests\Actions;

use Exception;
use Uniform\Form;
use Uniform\Tests\TestCase;
use Uniform\Actions\EmailSelectAction;
use Uniform\Exceptions\PerformerException;

class EmailSelectActionTest extends TestCase
{
    protected $form;
    public function setUp()
    {
        parent::setUp();
        $this->form = new Form;
    }

    public function testRecipientOptionRequired()
    {
        $action = new EmailSelectActionStub($this->form, [
            'to' => 'jane@user.com',
            'from' => 'infor@user.com',
        ]);
        $this->setExpectedException(Exception::class);
        $action->perform();
    }

    public function testRecipient()
    {
        $this->form->data('recipient', 'jane');
        $action = new EmailSelectActionStub($this->form, [
            'allowed-recipients' => ['jane' => 'jane@user.com'],
            'from' => 'infor@user.com',
        ]);
        $action->perform();
        $this->assertEquals('jane@user.com', $action->params['to']);
        $this->assertFalse(array_key_exists('recipient', $action->params));
    }

    public function testRecipientNotAllowed()
    {
        $this->form->data('recipient', 'joe');
        $action = new EmailSelectActionStub($this->form, [
            'allowed-recipients' => ['jane' => 'jane@user.com'],
            'from' => 'infor@user.com',
        ]);
        $this->setExpectedException(PerformerException::class);
        $action->perform();
    }
}

class EmailSelectActionStub extends EmailSelectAction
{
    protected function sendEmail(array $params)
    {
        $this->params = $params;
        return true;
    }
}
