<?php

namespace Jevets\Kirby\Flash\Tests;

use Mzur\Kirby\DefuseSession\Defuse;

class TestCase extends \PHPUnit\Framework\TestCase
{
    /**
     * Default preparation for each test.
     */
    public function setUp()
    {
        parent::setUp();
        Defuse::defuse();
    }
}
