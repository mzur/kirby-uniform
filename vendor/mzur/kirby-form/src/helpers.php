<?php

use Jevets\Kirby\Form;

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
