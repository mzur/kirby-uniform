<?php

namespace Jevets\Kirby\Flash\Tests;

use Mzur\Kirby\DefuseSession\Defuse;
use Jevets\Kirby\Flash;

class TestCase extends \PHPUnit\Framework\TestCase
{
    /**
     * Default preparation for each test.
     */
    public function setUp(): void
    {
        parent::setUp();
        Defuse::defuse();
        $this->nextPageLoad();
        // run two full page loads to ensure each test is isolated
        $this->nextPageLoad();
    }

    /**
     * Clears the stored static Flash instance, simulating a new page load
     */
    public function nextPageLoad()
    {
        $destroyInstance = function () {
            static::$instance = null;
        };
        $destroyInstance = $destroyInstance->bindTo(null, Flash::class);
        $destroyInstance();
    }
}
