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
        $this->nextPageLoad();
        $this->assertEquals('myvalue2', flash('mykey'));
    }

    public function testFlashNow()
    {
        flash('mykey', 'myvalue', true);
        $this->assertEquals('myvalue', flash('mykey'));
        $this->nextPageLoad();
        $this->assertNull(flash('mykey'));
    }
}
