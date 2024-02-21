<?php

namespace Uniform\Tests\Actions;

use Exception;
use Uniform\Form;
use Kirby\Cms\App;
use Uniform\Tests\TestCase;
use Uniform\Actions\EmailAction;
use Uniform\Exceptions\PerformerException;
use Kirby\Exception\NotFoundException;

class EmailActionTest extends TestCase
{
    protected $form;
    public function setUp(): void
    {
        parent::setUp();
        $this->form = new Form;
        App::instance()->extend([
            'templates' => [
                'emails/test' => __DIR__.'/../templates/test.php',
                'emails/test-data' => __DIR__.'/../templates/test-data.php',
                'emails/test-options' => __DIR__.'/../templates/test-options.php',
            ],
        ]);
    }

    public function testSenderOptionRequired()
    {
        $action = new EmailActionStub($this->form, ['to' => 'mail']);
        $this->expectException(Exception::class);
        $action->perform();
    }

    public function testToOptionRequired()
    {
        $action = new EmailActionStub($this->form, ['from' => 'mail']);
        $this->expectException(Exception::class);
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
        $email = $action->email;
        $this->assertEquals(['jane@user.com' => null], $email->to());
        $this->assertEquals('info@user.com', $email->from());
        $this->assertEquals('joe@user.com', $email->replyTo());
        $this->assertEquals('Message from the web form', $email->subject());
        $this->assertEquals('', $email->body()->text());
        $this->assertEquals('', $email->body()->html());
    }

    public function testReplyTo()
    {
        $action = new EmailActionStub($this->form, [
            'to' => 'jane@user.com',
            'from' => 'info@user.com',
            'replyTo' => 'joe@user.com',
        ]);
        $action->perform();
        $this->assertEquals('joe@user.com', $action->email->replyTo());
    }

    public function testSubject()
    {
        $action = new EmailActionStub($this->form, [
            'to' => 'jane@user.com',
            'from' => 'info@user.com',
            'subject' => 'My subject',
        ]);
        $action->perform();
        $this->assertEquals('My subject', $action->email->subject());
    }

    public function testPassthroughOptions()
    {
        $this->form->data('message', 'hello');
        $this->form->data('a', 3);
        $action = new EmailActionStub($this->form, [
            'to' => 'jane@user.com',
            'from' => 'info@user.com',
            'body' => ['text' => 'text', 'html' => 'html'],
            'cc' => ['janet@user.com', 'jessica@user.com'],
            'data' => ['a' => 1, 'b' => 2],
        ]);
        $action->perform();

        $email = $action->email;
        $this->assertEquals("Message: hello\n\nA: 3\n\n", $email->body()->text());
        $this->assertEquals(['janet@user.com' => null, 'jessica@user.com' => null], $email->cc());
        $expect = ['a' => 3, 'b' => 2, 'message' => 'hello'];
        $this->assertEquals($expect, $action->params['data']);
    }

    public function testEmailPresets()
    {
        App::instance()->extend([
            'options' => [
                'email' => [
                    'presets' => [
                        'default' => [
                            'from' => 'john@user.com',
                            'fromName' => 'John Doe'
                        ],
                    ],
                ],
            ],
        ]);

        $this->form->data('message', 'hello');
        $action = new EmailActionStub($this->form, [
            'preset' => 'default',
            'to' => 'jane@user.com',
            'fromName' => 'Janet Doe'
        ]);
        $action->perform();

        $email = $action->email;
        $this->assertEquals('john@user.com', $email->from());
        $this->assertEquals('Janet Doe', $email->fromName());
    }

    public function testEmailPresetNotDefined()
    {
        $action = new EmailActionStub($this->form, [
            'preset' => 'default',
            'to' => 'janet@user.com',
            'fromName' => 'Janet Doe'
        ]);

        $this->expectException(NotFoundException::class);
        $action->perform();
    }

    public function testSubjectTemplate()
    {
        $this->form->data('email', "joe@user.com");
        $this->form->data('name', "Joe\n\n");
        $this->form->data('data', ['somedata']);
        $action = new EmailActionStub($this->form, [
            'to' => 'jane@user.com',
            'from' => 'info@user.com',
            'subject' => 'Message from {{name}} with {{data}}',
        ]);
        $action->perform();
        $this->assertEquals('Message from Joe with ', $action->email->subject());
    }

    public function testBody()
    {
        $this->form->data('email', 'joe@user.com');
        $this->form->data('message', '<hello>');
        $this->form->data('data', ['some', 'data']);
        $action = new EmailActionStub($this->form, [
            'to' => 'jane@user.com',
            'from' => 'info@user.com',
        ]);
        $action->perform();
        $expect = "Message: &lt;hello&gt;\n\nData: some, data\n\n";
        $this->assertEquals($expect, $action->email->body()->text());
    }

    public function testBodyTemplate()
    {
        $this->form->data('email', 'joe@user.com');
        $this->form->data('name', 'Joe');
        $this->form->data('data', ['somedata']);
        $action = new EmailActionStub($this->form, [
            'to' => 'jane@user.com',
            'from' => 'info@user.com',
            'body' => "Hello\n{{name}} with {{data}}"
        ]);
        $action->perform();
        $this->assertEquals("Hello\nJoe with ", $action->email->body()->text());
    }

    public function testBodyEscapeHtml()
    {
        $this->form->data('email', 'joe@user.com');
        $this->form->data('message', '<hello>');
        $action = new EmailActionStub($this->form, [
            'to' => 'jane@user.com',
            'from' => 'info@user.com',
            'escapeHtml' => false,
        ]);
        $action->perform();
        $expect = "Message: <hello>\n\n";
        $this->assertEquals($expect, $action->email->body()->text());
    }

    public function testTemplate()
    {
        $this->form->data('email', 'joe@user.com');
        $action = new EmailActionStub($this->form, [
            'to' => 'jane@user.com',
            'from' => 'info@user.com',
            'template' => 'test',
        ]);
        $action->perform();
        $this->assertEquals('joe@user.com', $action->email->body()->text());
    }

    public function testTemplateOptions()
    {
        $action = new EmailActionStub($this->form, [
            'to' => 'jane@user.com',
            'from' => 'info@user.com',
            'template' => 'test-options',
        ]);
        $action->perform();
        $this->assertEquals('jane@user.com', $action->email->body()->text());
    }

    public function testTemplateData()
    {
        $this->form->data('email', 'joe@user.com');
        $this->form->data('message', '<hello>');
        $action = new EmailActionStub($this->form, [
            'to' => 'jane@user.com',
            'from' => 'info@user.com',
            'template' => 'test-data',
        ]);
        $action->perform();
        $this->assertEquals("&lt;hello&gt;\njoe@user.com", $action->email->body()->text());
    }

    public function testTemplateDataEscapeHtml()
    {
        $this->form->data('email', 'joe@user.com');
        $this->form->data('message', '<hello>');
        $action = new EmailActionStub($this->form, [
            'to' => 'jane@user.com',
            'from' => 'info@user.com',
            'template' => 'test-data',
            'escapeHtml' => false,
        ]);
        $action->perform();
        $this->assertEquals("<hello>\njoe@user.com", $action->email->body()->text());
    }

    public function testTemplateDefault()
    {
        $this->form->data('email', 'joe@user.com');
        $action = new EmailActionStub($this->form, [
            'to' => 'jane@user.com',
            'from' => 'info@user.com',
            'template' => 'uniform-default',
        ]);
        $action->perform();
        $this->assertEquals("Email: joe@user.com\n", $action->email->body()->text());
    }

    public function testTemplateTable()
    {
        $this->form->data('email', 'joe@user.com');
        $action = new EmailActionStub($this->form, [
            'to' => 'jane@user.com',
            'from' => 'info@user.com',
            'template' => 'uniform-table',
        ]);
        $action->perform();
        $text = $action->email->body()->text();
        $this->assertStringContainsString("joe@user.com", $text);
        $this->assertStringNotContainsString("_data", $text);
        $this->assertStringNotContainsString("_options", $text);
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
        $this->assertEquals(['jane@user.com' => null], $action->email->to());
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
        $email = $action->email;
        $this->assertEquals(['joe@user.com' => null], $email->to());
        $this->assertEquals('jane@user.com', $email->replyTo());
        $this->assertEquals('info@user.com', $email->from());
    }

    public function testHandleEmailExceptionNoDebug()
    {
        $this->form->data('field', 'value');
        $action = new EmailActionStub($this->form, [
            'service' => 'thrower',
            'to' => 'jane@user.com',
            'from' => 'info@user.com',
            'subject' => 'Test',
        ]);
        $action->shouldFail = true;

        try {
            $action->perform();
            $this->assertFalse(true);
        } catch (PerformerException $e) {
            $this->assertEquals('There was an error sending the form.', $e->getMessage());
        }
    }

    public function testHandleEmailExceptionDebug()
    {
        App::instance()->extend(['options' => ['debug' => true]]);
        $this->form->data('field', 'value');
        $action = new EmailActionStub($this->form, [
            'service' => 'thrower',
            'to' => 'jane@user.com',
            'from' => 'info@user.com',
            'subject' => 'Test',
        ]);
        $action->shouldFail = true;

        try {
            $action->perform();
            $this->assertFalse(true);
        } catch (PerformerException $e) {
            $this->assertEquals("There was an error sending the form: Failed", $e->getMessage());
        }
    }
}

class EmailActionStub extends EmailAction
{
    public $calls = 0;
    protected function sendEmail(array $params)
    {
        $this->calls++;
        $this->params = $params;
        if (isset($this->shouldFail)) {
            throw new Exception('Failed');
        } else {
            $this->email = App::instance()->email($params, ['debug' => true]);
        }
    }
}
