<?php

namespace Jevets\Kirby\Flash\Tests;

use Jevets\Kirby\Flash;

class FlashTest extends TestCase
{

    public function testGetInstance()
    {
        $flash = Flash::getInstance();
        $this->assertInstanceof(Flash::class, $flash);
    }

    public function testSessionKey()
    {
        $this->assertEquals('_flash', Flash::sessionKey());
    }

    public function testSetSessionKey()
    {
        Flash::setSessionKey('_myflash');
        $this->assertEquals('_myflash', Flash::sessionKey());
    }

    public function testSetGetAll()
    {
        $flash = Flash::getInstance();
        $flash->set('key', 'value');
        $this->assertEquals('value', $flash->get('key'));
        $this->assertEquals('default', $flash->get('key2', 'default'));
        $this->assertNull($flash->get('key3'));
        $this->assertEquals(['key' => 'value'], $flash->all());
    }

    public function testInstances()
    {
        $flash = Flash::getInstance();
        $flash2 = new Flash('flash2');
        $flash3 = new Flash('flash3');

        $flash->set('key', 'value');
        $flash2->set('key', 'value2');
        $flash3->set('key', 'value3');

        $this->assertEquals('value', $flash->get('key'));
        $this->assertEquals('value2', $flash2->get('key'));
        $this->assertEquals('value3', $flash3->get('key'));
    }
}
