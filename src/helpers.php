<?php

use Uniform\Guards\HoneypotGuard;

if (!function_exists('csrf_field')) {
    /**
     * Generate a CSRF token form field.
     *
     */
    function csrf_field()
    {
        return '<input type="hidden" name="_token" value="'.csrf().'">';
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
