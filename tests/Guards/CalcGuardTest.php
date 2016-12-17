<?php

namespace Uniform\Tests\Guards;

use S;
use Uniform\Form;
use Uniform\Tests\TestCase;
use Uniform\Guards\CalcGuard;
use Uniform\Exceptions\PerformerException;

class CalcGuardTest extends TestCase
{
    public function testPerform()
    {
        S::set(CalcGuard::FLASH_KEY, 5);
        $_POST[CalcGuard::FIELD_NAME] = 5;
        $guard = new CalcGuard(new Form);
        $guard->perform();
    }

    public function testPerformField()
    {
        S::set(CalcGuard::FLASH_KEY, 5);
        $_POST['calc'] = 5;
        $guard = new CalcGuard(new Form, ['field' => 'calc']);
        $guard->perform();
    }

    public function testFailWrong()
    {
        S::set(CalcGuard::FLASH_KEY, 5);
        $_POST[CalcGuard::FIELD_NAME] = 4;
        $guard = new CalcGuard(new Form);
        $this->setExpectedException(PerformerException::class);
        $guard->perform();
    }

    public function testFailEmpty()
    {
        S::set(CalcGuard::FLASH_KEY, 5);
        $guard = new CalcGuard(new Form);
        $this->setExpectedException(PerformerException::class);
        $guard->perform();
    }
}
