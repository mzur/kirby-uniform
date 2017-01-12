<?php

use Uniform\Form;
use Uniform\Guards\CalcGuard;
use Uniform\Guards\HoneypotGuard;

if (!function_exists('csrf_field')) {
    /**
     * Generate a CSRF token form field.
     *
     * This function can be called multiple times and will reuse the same token during a
     * single request.
     *
     * @param string $t The CSRF token to use. If empty a new one will be generated and reused for the duration of a request.
     *
     * @return string
     */
    function csrf_field($t = null)
    {
        // remember the token for multipme function calls
        static $token = null;
        $token = $token ?: csrf();
        // the token parameter overrides the generated token
        return '<input type="hidden" name="'.Form::CSRF_FIELD.'" value="'.($t ?: $token).'">';
    }
}

if (!function_exists('honeypot_field')) {
    /**
     * Generate a honeypot form field.
     *
     * @param string $name Name of the honeypot field
     * @param string $class CSS class of the honeypot field
     *
     * @return string
     */
    function honeypot_field($name = null, $class = null)
    {
        $name = $name ?: HoneypotGuard::FIELD_NAME;
        $class = $class ?: 'uniform__potty';
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
     * @param string $name Form field name
     * @param string $class Form field CSS class
     *
     * @return string
     */
    function captcha_field($name = null, $class = null)
    {
        $name = $name ?: CalcGuard::FIELD_NAME;
        $class = $class ?: 'uniform__captcha';
        return '<input type="number" name="'.$name.'" class="'.$class.'">';
    }
}
