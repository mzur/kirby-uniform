<?php

namespace Uniform\Tests;

class HelperTest extends TestCase
{
    public function testFunction()
    {
        $this->assertTrue(function_exists('csrf_field'));
        $this->assertTrue(function_exists('honeypot_field'));
        $this->assertTrue(function_exists('uniform_captcha'));
        $this->assertTrue(function_exists('captcha_field'));
    }

    public function testCsrfField()
    {
        // the token should not be regenerated during a single request
        $this->assertEquals(csrf_field(), csrf_field());
        $this->assertContains('value="abc"', csrf_field('abc'));
    }
}
