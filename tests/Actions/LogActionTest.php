<?php

namespace Uniform\Tests\Actions;

use Exception;
use Kirby\Cms\App;
use Kirby\Http\Environment;
use Uniform\Actions\LogAction;
use Uniform\Exceptions\PerformerException;
use Uniform\Form;
use Uniform\Tests\TestCase;

class LogActionTest extends TestCase
{
    protected $form;
    public function setUp(): void
    {
        parent::setUp();
        $this->form = new Form;
    }

    public function testFileOptionRequired()
    {
        $action = new LogActionStub($this->form);
        $this->expectException(Exception::class);
        $action->perform();
    }

    public function testPerform()
    {
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
        $_SERVER['HTTP_USER_AGENT'] = 'Mozilla';
        App::instance()->environment()->detect();
        App::instance()->clone();
        $this->form->data('message', '<hello>');
        $this->form->data('data', ['some', 'data']);
        $action = new LogActionStub($this->form, ['file' => '/dev/null']);
        $action->perform();
        $this->assertEquals('/dev/null', $action->filename);
        $this->assertStringContainsString('['.date('c').'] 127.0.0.1 Mozilla', $action->content);
        $this->assertStringContainsString('message: &lt;hello&gt;', $action->content);
        $this->assertStringContainsString('data: some, data', $action->content);
    }

    public function testPerformEscapeHtml()
    {
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
        $_SERVER['HTTP_USER_AGENT'] = 'Mozilla';
        App::instance()->environment()->detect();
        App::instance()->clone();
        $this->form->data('message', '<hello>');
        $action = new LogActionStub($this->form, [
            'file' => '/dev/null',
            'escapeHtml' => false,
        ]);
        $action->perform();
        $this->assertEquals('/dev/null', $action->filename);
        $this->assertStringContainsString('['.date('c').'] 127.0.0.1 Mozilla', $action->content);
        $this->assertStringContainsString('message: <hello>', $action->content);
    }

    public function testPerformTemplate()
    {
        $this->form->data('message', '<hello>');
        $action = new LogActionStub($this->form, [
            'file' => '/dev/null',
            'template' => 'uniform/log-json',
        ]);
        $action->perform();
        $this->assertStringContainsString('"timestamp"', $action->content);
        $this->assertStringContainsString('"ip"', $action->content);
        $this->assertStringContainsString('"userAgent"', $action->content);
        $this->assertStringContainsString('"&lt;hello&gt;"', $action->content);
        $this->assertStringStartsWith('{', $action->content);
        $this->assertStringEndsWith('}', $action->content);
    }

    public function testPerformTemplateEscapeHtml()
    {
        $this->form->data('message', '<hello>');
        $action = new LogActionStub($this->form, [
            'file' => '/dev/null',
            'template' => 'uniform/log-json',
            'escapeHtml' => false,
        ]);
        $action->perform();
        $this->assertStringContainsString('"timestamp"', $action->content);
        $this->assertStringContainsString('"ip"', $action->content);
        $this->assertStringContainsString('"userAgent"', $action->content);
        $this->assertStringContainsString('"<hello>"', $action->content);
        $this->assertStringStartsWith('{', $action->content);
        $this->assertStringEndsWith('}', $action->content);
    }

    public function testFail()
    {
        $action = new LogActionStub($this->form, ['file' => '/dev/null']);
        $action->shouldFail = true;
        $this->expectException(PerformerException::class);
        $action->perform();
    }
}

class LogActionStub extends LogAction
{
    protected function write($filename, $content)
    {
        $this->filename = $filename;
        $this->content = $content;
        return !isset($this->shouldFail);
    }
}
