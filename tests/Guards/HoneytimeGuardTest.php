<?php

namespace Uniform\Tests\Guards;

use Uniform\Form;
use Uniform\Tests\TestCase;
use Uniform\Guards\HoneytimeGuard;
use Uniform\Exceptions\PerformerException;

class HoneytimeGuardTest extends TestCase
{
    public $key = 'base64:m9pAO+r/7SbyT0lfWTYM4+iV9BwZiT3ouxBurDoNAXs=';

    public function testPerform()
    {
        $ciphertext = HoneytimeGuard::encrypt($this->key, strval(time()));
        $_POST['uniform-honeytime'] = $ciphertext;
        $guard = new HoneytimeGuard(new Form, [
            'key' => $this->key,
            'seconds' => -1,
        ]);
        $guard->perform();
        $this->assertTrue(true);
    }

    public function testPerformField()
    {
        $ciphertext = HoneytimeGuard::encrypt($this->key, strval(time()));
        $_POST['field'] = $ciphertext;
        $guard = new HoneytimeGuard(new Form, [
            'key' => $this->key,
            'seconds' => -1,
            'field' => 'field',
        ]);
        $guard->perform();
        $this->assertTrue(true);
    }

    public function testTooFast()
    {
        $ciphertext = HoneytimeGuard::encrypt($this->key, strval(time()));
        $_POST['uniform-honeytime'] = $ciphertext;
        $guard = new HoneytimeGuard(new Form, [
            'key' => $this->key,
        ]);
        $this->expectException(PerformerException::class);
        $guard->perform();
    }

    public function testEmpty()
    {
        $_POST['uniform-honeytime'] = '';
        $guard = new HoneytimeGuard(new Form, [
            'key' => $this->key,
            'seconds' => 0,
        ]);
        $this->expectException(PerformerException::class);
        $guard->perform();
    }

    public function testWrong()
    {
        $_POST['uniform-honeytime'] = 'abcdefg';
        $guard = new HoneytimeGuard(new Form, [
            'key' => $this->key,
            'seconds' => 0,
        ]);
        $this->expectException(PerformerException::class);
        $guard->perform();
    }
}
