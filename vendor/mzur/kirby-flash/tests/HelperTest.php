<?php

namespace Jevets\Kirby\Flash\Tests;

class HelperTest extends TestCase
{
    public function testFunction()
    {
        $this->assertTrue(function_exists('flash'));
    }

    public function testFlash()
    {
        flash('mykey', 'myvalue');
        $this->assertEquals('myvalue', flash('mykey'));
        flash('mykey', 'myvalue2');
        $this->assertEquals('myvalue2', flash('mykey'));
    }
}
