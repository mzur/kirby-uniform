<?php

namespace Uniform\Tests\Actions;

use Error;
use Exception;
use C as Config;
use Uniform\Form;
use Uniform\Tests\TestCase;
use Uniform\Actions\EmailAction;
use Uniform\Exceptions\PerformerException;

class EmailActionTest extends TestCase
{
    protected $form;
    public function setUp()
    {
        parent::setUp();
        $this->form = new Form;
    }

    public function testSenderOptionRequired()
    {
        $action = new EmailActionStub($this->form, ['to' => 'mail']);
        $this->setExpectedException(Exception::class);
        $action->perform();
    }

    public function testToOptionRequired()
    {
        $action = new EmailActionStub($this->form, ['from' => 'mail']);
        $this->setExpectedException(Exception::class);
        $action->perform();
    }

    public function testPerform()
    {
        $this->form->data('email', 'joe@user.com');
        $action = new EmailActionStub($this->form, [
            'to' => 'jane@user.com',
            'from' => 'info@user.com',
        ]);
        $action->perform();
        $expected = [
            'service' => 'mail',
            'options' => [],
            'to' => 'jane@user.com',
            'from' => 'info@user.com',
            'replyTo' => 'joe@user.com',
            'subject' => 'Message from the web form',
            'body' => '',
        ];
        $this->assertEquals($expected, $action->params);
    }

    public function testFail()
    {
        $action = new EmailActionStub($this->form, [
            'to' => 'jane@user.com',
            'from' => 'info@user.com',
        ]);
        $action->shouldFail = true;
        $this->setExpectedException(PerformerException::class);
        $action->perform();
    }

    public function testReplyTo()
    {
        $action = new EmailActionStub($this->form, [
            'to' => 'jane@user.com',
            'from' => 'info@user.com',
            'replyTo' => 'joe@user.com',
        ]);
        $action->perform();
        $this->assertEquals('joe@user.com', $action->params['replyTo']);
    }

    public function testService()
    {
         $action = new EmailActionStub($this->form, [
            'to' => 'jane@user.com',
            'from' => 'info@user.com',
            'service' => 'aws',
            'service-options' => ['someoptions'],
        ]);
        $action->perform();
        $this->assertEquals('aws', $action->params['service']);
        $this->assertEquals(['someoptions'], $action->params['options']);
    }

    public function testSubjectTemplate()
    {
        $this->form->data('email', "joe@user.com\n\n");
        $this->form->data('data', ['somedata']);
        $action = new EmailActionStub($this->form, [
            'to' => 'jane@user.com',
            'from' => 'info@user.com',
            'subject' => 'Message from {email} with {data}',
        ]);
        $action->perform();
        $this->assertEquals('Message from joe@user.com with {data}', $action->params['subject']);
    }

    public function testBody()
    {
        $this->form->data('email', 'joe@user.com');
        $this->form->data('message', 'hello');
        $this->form->data('data', ['some', 'data']);
        $action = new EmailActionStub($this->form, [
            'to' => 'jane@user.com',
            'from' => 'info@user.com',
        ]);
        $action->perform();
        $expect = "Message: hello\n\nData: some, data\n\n";
        $this->assertEquals($expect, $action->params['body']);
    }

    public function testBodySnippet()
    {
        $action = new EmailActionStub($this->form, [
            'to' => 'jane@user.com',
            'from' => 'info@user.com',
            'snippet' => 'my snippet',
        ]);
        $action->perform();
        $this->assertEquals('my snippet', $action->params['body']);
    }

    public function testSnippetData()
    {
        $this->form->data('email', 'joe@user.com');
        $action = new EmailActionStub($this->form, [
            'to' => 'jane@user.com',
            'from' => 'info@user.com',
            'snippet' => 'my snippet',
        ]);
        $action->perform();
        $this->assertEquals('joe@user.com', $action->data['data']['email']);
        $this->assertEquals('info@user.com', $action->data['options']['from']);
    }

    public function testReceiveCopyDisabled()
    {
        $this->form->data('email', 'joe@user.com');
        $this->form->data('receive_copy', '1');
        $action = new EmailActionStub($this->form, [
            'to' => 'jane@user.com',
            'from' => 'info@user.com',
        ]);
        $action->perform();
        $this->assertEquals(1, $action->calls);
        $this->assertEquals('jane@user.com', $action->params['to']);
    }

    public function testReceiveCopy()
    {
        $this->form->data('email', 'joe@user.com');
        $action = new EmailActionStub($this->form, [
            'to' => 'jane@user.com',
            'from' => 'info@user.com',
            'receive-copy' => true,
        ]);
        $action->perform();
        $this->assertEquals(1, $action->calls);
        $this->form->data('receive_copy', '1');
        $action->perform();
        $this->assertEquals(3, $action->calls);
        $this->assertEquals('joe@user.com', $action->params['to']);
        $this->assertEquals('jane@user.com', $action->params['replyTo']);
        $this->assertEquals('info@user.com', $action->params['from']);
    }

    public function testHandleServiceException()
    {
        $this->form->data('field', 'value');
        $action = new EmailAction($this->form, [
            'service' => 'thrower',
            'to' => 'jane@user.com',
            'from' => 'info@user.com',
            'subject' => 'Test',
        ]);

        Config::set('debug', false);

        try {
            $action->perform();
            $this->assertFalse(true);
        } catch (PerformerException $e) {
            $this->assertEquals('There was an error sending the form.', $e->getMessage());
        }

        Config::set('debug', true);

        try {
            $action->perform();
            $this->assertFalse(true);
        } catch (PerformerException $e) {
            $this->assertEquals("There was an error sending the form: Throw it like it's hoot", $e->getMessage());
        }
    }

    public function testHandleServiceError()
    {
        $this->form->data('field', 'value');
        $action = new EmailAction($this->form, [
            'service' => 'thrower2',
            'to' => 'jane@user.com',
            'from' => 'info@user.com',
            'subject' => 'Test',
        ]);

        Config::set('debug', false);

        try {
            $action->perform();
            $this->assertFalse(true);
        } catch (PerformerException $e) {
            $this->assertEquals('There was an error sending the form.', $e->getMessage());
        }

        Config::set('debug', true);

        try {
            $action->perform();
            $this->assertFalse(true);
        } catch (PerformerException $e) {
            $this->assertEquals("There was an error sending the form: Throw it like it's hoot", $e->getMessage());
        }
    }
}

class EmailActionStub extends EmailAction
{
    public $calls = 0;
    public $data;
    protected function sendEmail(array $params)
    {
        $this->calls++;
        $this->params = $params;
        if (isset($this->shouldFail)) {
            throw new Exception('Failed');
        }
    }

    protected function getSnippet($name, array $data)
    {
        if (!array_key_exists('data', $data) || !array_key_exists('options', $data)) {
            throw new Exception;
        }

        $this->data = $data;

        return $name;
    }
}

\Email::$services['thrower'] = function($email) {
    throw new Exception("Throw it like it's hoot");
};

\Email::$services['thrower2'] = function($email) {
    throw new Error("Throw it like it's hoot");
};
