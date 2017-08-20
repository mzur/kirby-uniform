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

if (class_exists('v')) {
    // Extended validation rules for file uploads.
    v::$validators['file'] = function ($value) {
        return is_array($value) && array_key_exists('tmp_name', $value) && is_uploaded_file($value['tmp_name']);
    };

    v::$validators['filesize'] = function ($value, $size) {
        // $size is in kb and $value['size'] is in byte, so multiply by 1000
        return is_array($value) && array_key_exists('size', $value) && $value['size'] <= $size * 1000;
    };

    v::$validators['mime'] = function ($value, $allowed) {
        if (is_string($value)) {
          $name = $value;
        } elseif (is_array($value) && array_key_exists('tmp_name', $value)) {
          // This is for uploaded files from $_FILES
          $name = $value['tmp_name'];
        }
        if (isset($name)) {
          return in_array(f::mime($name), $allowed);
        }
        return false;
    };

    v::$validators['image'] = function ($value) {
        if (is_string($value)) {
          $name = $value;
        } elseif (is_array($value) && array_key_exists('tmp_name', $value)) {
          // This is for uploaded files from $_FILES
          $name = $value['tmp_name'];
        }
        if (isset($name)) {
          return f::type($name) === 'image';
        }
        return false;
    };
}
