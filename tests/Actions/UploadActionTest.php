<?php

namespace Uniform\Tests\Actions;

use Uniform\Form;
use Uniform\Tests\TestCase;
use Uniform\Actions\UploadAction;
use Uniform\Exceptions\PerformerException;

class UploadActionTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();
        $this->dir = sys_get_temp_dir();
        $this->form = new Form;
    }

    public function testSkipMissingFile()
    {
        $action = new UploadActionStub($this->form, ['fields' => ['testfield' => []]]);
        $action->perform();
        $this->assertNull($action->source);
        $this->assertNull($action->target);
    }

    public function testTargetRequired()
    {
        $_FILES['testfield'] = [];
        $action = new UploadActionStub($this->form, ['fields' => ['testfield' => []]]);
        $this->expectException(PerformerException::class);
        $this->expectExceptionMessage('target directory is missing');
        $action->perform();
    }

    public function testTargetDirIsFile()
    {
        $path = $this->dir.'/uniform_abc123';
        touch($path);
        $_FILES['testfield'] = [];
        $action = new UploadActionStub($this->form, ['fields' => [
            'testfield' => ['target' => $path],
        ]]);
        $this->expectException(PerformerException::class);
        $this->expectExceptionMessage('Could not create target directory');
        try {
            $action->perform();
        } catch (PerformerException $e) {
            unlink($path);
            throw $e;
        }
    }

    public function testTargetFileIsFile()
    {
        $path = $this->dir.'/uniform_abc123';
        @mkdir($path);
        touch("{$path}/myfile.txt");
        $_FILES['testfield'] = ['name' => 'myfile.txt'];
        $action = new UploadActionStub($this->form, ['fields' => [
            'testfield' => ['target' => $path, 'prefix' => false],
        ]]);
        $this->expectException(PerformerException::class);
        $this->expectExceptionMessage('file already exists');
        try {
            $action->perform();
        } catch (PerformerException $e) {
            unlink("{$path}/myfile.txt");
            rmdir($path);
            throw $e;
        }
    }

    public function testTargetFileIsFileWithPrefix()
    {
        $path = $this->dir.'/uniform_abc123';
        @mkdir($path);
        touch("{$path}/prefixmyfile.txt");
        $_FILES['testfield'] = ['name' => 'myfile.txt'];
        $action = new UploadActionStub($this->form, ['fields' => [
            'testfield' => ['target' => $path, 'prefix' => 'prefix'],
        ]]);
        $this->expectException(PerformerException::class);
        $this->expectExceptionMessage('file already exists');
        try {
            $action->perform();
        } catch (PerformerException $e) {
            unlink("{$path}/prefixmyfile.txt");
            rmdir($path);
            throw $e;
        }
    }

    public function testHandleRollback()
    {
        $_FILES['testfield'] = [
            'tmp_name' => $this->dir.'/uniform_123abc',
            'name' => 'myfile.txt'
        ];
        $action = new UploadActionStub($this->form, ['fields' => [
            'testfield' => ['target' => $this->dir.'/uniform_test'],
        ]]);
        // First call tests default behavior.
        $action->perform();
        $this->assertEquals($this->dir.'/uniform_123abc', $action->source);
        $this->assertStringStartsWith($this->dir.'/uniform_test', $action->target);
        $this->assertStringEndsWith('myfile.txt', $action->target);
        // 2 for "." and "..", 1 for expected file
        $this->assertEquals(3, count(scandir($this->dir.'/uniform_test')));
        $this->assertTrue(is_dir($this->dir.'/uniform_test'));

        // Second call simulates error with a second file. Action should roll back
        // stuff of first file.
        $action->success = false;
        $this->expectException(PerformerException::class);
        $this->expectExceptionMessage('file could not be uploaded');

        try {
            $action->perform();
        } catch (PerformerException $e) {
            $this->assertFalse(is_file($this->dir.'/uniform_test/myfile.txt'));
            $this->assertFalse(is_dir($this->dir.'/uniform_test'));
            throw $e;
        }
    }
}

class UploadActionStub extends UploadAction
{
    public $success = true;
    public $source = null;
    public $target = null;
    protected function moveFile($source, $target)
    {
        $this->source = $source;
        $this->target = $target;
        if ($this->success) touch($target);

        return $this->success;
    }
}
