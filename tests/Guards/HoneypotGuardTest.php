<?php

namespace Uniform\Tests\Guards;

use Uniform\Form;
use Uniform\Tests\TestCase;
use Uniform\Guards\HoneypotGuard;
use Uniform\Exceptions\PerformerException;

class HoneypotGuardTest extends TestCase
{
    public function testPerform()
    {
        $_POST['website'] = '';
        $guard = new HoneypotGuard(new Form);
        $guard->perform();
    }

    public function testPerformField()
    {
        $_POST['url'] = '';
        $guard = new HoneypotGuard(new Form, ['field' => 'url']);
        $guard->perform();
    }

    public function testFail()
    {
        $_POST['url'] = '';
        $guard = new HoneypotGuard(new Form);
        $this->setExpectedException(PerformerException::class);
        $guard->perform();
    }
}
