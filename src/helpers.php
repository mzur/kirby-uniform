<?php

use Uniform\Form;
use Uniform\Guards\CalcGuard;
use Uniform\Guards\HoneypotGuard;

if (!function_exists('csrf_field')) {
    /**
     * Generate a CSRF token form field.
     *
     */
    function csrf_field()
    {
        return '<input type="hidden" name="'.Form::CSRF_FIELD.'" value="'.csrf().'">';
    }
}

if (!function_exists('honeypot_field')) {
    /**
     * Generate a honeypot form field.
     *
     */
    function honeypot_field($name = HoneypotGuard::FIELD_NAME, $class = 'uniform__potty')
    {
        return '<input type="text" name="'.$name.'" class="'.$class.'">';
    }
}

if (!function_exists('uniform_captcha')) {
    /**
     * Generate a new calc guard result for a Uniform form.
     *
     * @return string Something like '4 plus 5'
     */
    function uniform_captcha()
    {
        list($a, $b) = [rand(0, 9), rand(0, 9)];
        s::set(CalcGuard::FLASH_KEY, $a + $b);

        return str::encode($a.' '.l::get('uniform-calc-plus').' '.$b);
    }
}

if (!function_exists('captcha_field')) {
    /**
     * Generate a calc guard form field.
     *
     */
    function captcha_field($name = CalcGuard::FIELD_NAME, $class = 'uniform__captcha')
    {
        return '<input type="number" name="'.$name.'" class="'.$class.'">';
    }
}
