<?php

namespace Uniform\Tests\Actions;

use Exception;
use Uniform\Form;
use Uniform\Tests\TestCase;
use Uniform\Actions\LogAction;
use Uniform\Exceptions\PerformerException;

class LogActionTest extends TestCase
{
    protected $form;
    public function setUp()
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
        putenv('REMOTE_ADDR=127.0.0.1');
        $_SERVER['HTTP_USER_AGENT'] = 'Mozilla';
        $this->form->data('message', 'hello');
        $this->form->data('data', ['some', 'data']);
        $action = new LogActionStub($this->form, ['file' => '/dev/null']);
        $action->perform();
        $this->assertEquals('/dev/null', $action->filename);
        $this->assertContains('['.date('c').'] 127.0.0.1 Mozilla', $action->content);
        $this->assertContains('message: hello', $action->content);
        $this->assertContains('data: some, data', $action->content);
    }

    public function testPerformTemplate()
    {
        $action = new LogActionStub($this->form, [
            'file' => '/dev/null',
            'template' => 'uniform/log-json',
        ]);
        $action->perform();
        $this->assertContains('"timestamp"', $action->content);
        $this->assertContains('"ip"', $action->content);
        $this->assertContains('"userAgent"', $action->content);
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
