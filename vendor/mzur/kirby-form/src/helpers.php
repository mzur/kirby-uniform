<?php

use Kirby\Toolkit\V;
use Kirby\Toolkit\F;
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

if (class_exists(V::class)) {
    // Extended validation rules for file uploads.
    V::$validators['file'] = function ($value) {
        return is_array($value) &&
            array_key_exists('name', $value) &&
            array_key_exists('type', $value) &&
            array_key_exists('size', $value) &&
            array_key_exists('tmp_name', $value) &&
            array_key_exists('error', $value) &&
                ($value['error'] === UPLOAD_ERR_OK || $value['error'] === UPLOAD_ERR_NO_FILE);
    };

    V::$validators['requiredFile'] = function ($value) {
        return V::file($value) && $value['error'] === UPLOAD_ERR_OK;
    };

    V::$validators['filesize'] = function ($value, $size) {
        // $size is in kb and $value['size'] is in byte, so multiply by 1000
        return is_array($value) &&
            array_key_exists('size', $value) &&
            array_key_exists('error', $value) &&
            (($value['size'] <= $size * 1000) || $value['error'] === UPLOAD_ERR_NO_FILE);
    };

    V::$validators['mime'] = function ($value, $allowed) {
        if (!is_array($allowed)) {
            $allowed = array_slice(func_get_args(), 1);
        }
        if (is_string($value)) {
          $name = $value;
        } elseif (is_array($value) && array_key_exists('tmp_name', $value) && array_key_exists('error', $value)) {
          // This is for uploaded files from $_FILES
          $name = $value['tmp_name'];
          if ($value['error'] === UPLOAD_ERR_NO_FILE) {
            return true;
          }
        }
        if (isset($name)) {
          return in_array(F::mime($name), $allowed);
        }
        return false;
    };

    V::$validators['image'] = function ($value) {
        if (is_string($value)) {
          $name = $value;
        } elseif (is_array($value) && array_key_exists('tmp_name', $value)  && array_key_exists('error', $value)) {
          // This is for uploaded files from $_FILES
          $name = $value['tmp_name'];
          if ($value['error'] === UPLOAD_ERR_NO_FILE) {
            return true;
          }
        }
        if (isset($name)) {
          return F::type($name) === 'image';
        }
        return false;
    };
}
