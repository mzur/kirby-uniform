<?php

namespace Jevets\Kirby\Form\Tests;

use Jevets\Kirby\Form;
use Jevets\Kirby\Flash;
use Mzur\Kirby\DefuseSession\Defuse;

class TestCase extends \PHPUnit\Framework\TestCase
{
    /**
     * Default preparation for each test.
     */
    public function setUp(): void
    {
        parent::setUp();
        Defuse::defuse();
        $flash = Flash::getInstance();
        $flash->set(Form::FLASH_KEY_DATA, null);
        $flash->set(Form::FLASH_KEY_ERRORS, null);
        $_POST = [];
        $_FILES = [];
    }
}
