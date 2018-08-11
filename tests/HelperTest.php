<?php

namespace Uniform\Tests;

class HelperTest extends TestCase
{
    public function testFunction()
    {
        $this->assertTrue(function_exists('honeypot_field'));
        honeypot_field();
        $this->assertTrue(function_exists('uniform_captcha'));
        uniform_captcha();
        $this->assertTrue(function_exists('captcha_field'));
        captcha_field();
    }
}
