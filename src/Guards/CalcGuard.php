<?php

namespace Uniform\Guards;

use L;
use S;
use R;

/**
 * Guard that checks a simple arithmetic problem.
 */
class CalcGuard extends Guard
{
    /**
     * Session key for the captcha result
     *
     * @var string
     */
    const FLASH_KEY = 'uniform-captcha-result';

    /**
     * Captcha field name
     *
     * @var string
     */
    const FIELD_NAME = 'captcha';

    /**
     * {@inheritDoc}
     * Check if the captcha field was filled in correctly
     * Remove the field from the form data if it was correct.
     */
    public function perform()
    {
        $field = $this->option('field', self::FIELD_NAME);
        $result = S::get(self::FLASH_KEY, null);
        if ($result === null || R::postData($field) != $result) {
            $this->reject(L::get('uniform-calc-incorrect'), $field);
        }
        $this->form->forget($field);
    }
}
