<?php

use Jevets\Kirby\Flash;

if (!function_exists('flash')) {
    /**
     * Flash data to the session. Data is only available for
     * to the next page load.
     *
     * If called without a second paramer, data is returned.
     * If called with a second parameter, data is set.
     *
     * @param string $key
     * @param mixed optional $value to set
     * @param boolean optional set $value for current page load only
     */
    function flash($key, $setValue = '', $now = false)
    {
        $flash = Flash::getInstance();

        if ($setValue) {
            $flash->set($key, $setValue, $now);
            return $setValue;
        } else {
            return $flash->get($key);
        }
    }
}
