<?php

namespace Uniform\Tests;

use Jevets\Kirby\Form;
use Jevets\Kirby\Flash;

// Dirty hack to run tests even if s::start() of the Kirby Toolkit is called
// see: http://stackoverflow.com/a/4059399/1796523
ob_start();

require_once __DIR__.DS.'..'.DS.'languages'.DS.'en.php';

class TestCase extends \PHPUnit_Framework_TestCase
{
    /**
     * Default preparation for each test.
     */
    public function setUp()
    {
        parent::setUp();
        $flash = Flash::getInstance();
        $flash->set(Form::FLASH_KEY_DATA, null);
        $flash->set(Form::FLASH_KEY_ERRORS, null);
        $_POST = [];
    }
}
