<?php

namespace Jevets\Kirby\Form\Tests;

use Kirby\Cms\App;
use Jevets\Kirby\Form;
use Jevets\Kirby\Flash;

class TestCase extends \PHPUnit\Framework\TestCase
{
    /**
     * Default preparation for each test.
     */
    public function setUp()
    {
        parent::setUp();
        App::instance(new SessionTestApp);
        $flash = Flash::getInstance();
        $flash->set(Form::FLASH_KEY_DATA, null);
        $flash->set(Form::FLASH_KEY_ERRORS, null);
        $_POST = [];
        $_FILES = [];
    }
}
