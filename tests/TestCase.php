<?php

namespace Uniform\Tests;

// Dirty hack to run tests even if s::start() of the Kirby Toolkit is called
// see: http://stackoverflow.com/a/4059399/1796523
ob_start();

class TestCase extends \PHPUnit_Framework_TestCase
{
    /**
     * Default preparation for each test.
     */
    public function setUp()
    {
        parent::setUp();
    }

    protected function mockToolkit($class)
    {
        $mock = \Mockery::mock($class);
        $ref = new \ReflectionProperty($class, 'set');
        $ref->setAccessible(true);
        $ref->setValue(null, function ($key, $value) {

        });

        return $mock;
    }
}
