<?php

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
