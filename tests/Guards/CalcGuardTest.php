<?php

namespace Uniform\Tests\Guards;

use Uniform\Form;
use Kirby\Cms\App;
use Uniform\Tests\TestCase;
use Uniform\Guards\CalcGuard;
use Uniform\Exceptions\PerformerException;

class CalcGuardTest extends TestCase
{
    public function testPerform()
    {
        App::instance()->session()->set(CalcGuard::FLASH_KEY, 5);
        $_POST[CalcGuard::FIELD_NAME] = 5;
        $guard = new CalcGuard(new Form);
        $guard->perform();
        $this->assertTrue(true);
    }

    public function testPerformField()
    {
        App::instance()->session()->set(CalcGuard::FLASH_KEY, 5);
        $_POST['calc'] = 5;
        $guard = new CalcGuard(new Form, ['field' => 'calc']);
        $guard->perform();
        $this->assertTrue(true);
    }

    public function testFailWrong()
    {
        App::instance()->session()->set(CalcGuard::FLASH_KEY, 5);
        $_POST[CalcGuard::FIELD_NAME] = 4;
        $guard = new CalcGuard(new Form);
        $this->expectException(PerformerException::class);
        $guard->perform();
    }

    public function testFailEmpty()
    {
        App::instance()->session()->set(CalcGuard::FLASH_KEY, 5);
        $guard = new CalcGuard(new Form);
        $this->expectException(PerformerException::class);
        $guard->perform();
    }
}
