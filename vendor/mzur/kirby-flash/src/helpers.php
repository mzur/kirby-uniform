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
     */
    function flash($key, $setValue = '')
    {
        $flash = Flash::getInstance();

        if ($setValue) {
            $flash->set($key, $setValue);
            return $setValue;
        } else {
            return $flash->get($key);
        }
    }
}
