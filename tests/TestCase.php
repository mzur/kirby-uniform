<?php

namespace Uniform\Tests;

use Jevets\Kirby\Form;
use Jevets\Kirby\Flash;

class TestCase extends \PHPUnit\Framework\TestCase
{
    /**
     * Default preparation for each test.
     *
     */
    public function setUp(): void
    {
        parent::setUp();
        $flash = Flash::getInstance();
        $flash->set(Form::FLASH_KEY_DATA, null);
        $flash->set(Form::FLASH_KEY_ERRORS, null);
        $_POST = [];
    }
}
