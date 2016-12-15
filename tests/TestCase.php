<?php

namespace Uniform\Tests;

class TestCase extends \PHPUnit_Framework_TestCase
{
    /**
     * Default preparation for each test.
     */
    public function setUp()
    {
        parent::setUp();
        $_POST = [];
    }
}
