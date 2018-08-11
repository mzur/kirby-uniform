<?php

namespace Uniform\Guards;

use Kirby\Cms\App;
use Kirby\Toolkit\I18n;

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
        $result = App::instance()->session()->get(self::FLASH_KEY, null);
        if ($result === null || App::instance()->request()->body()->get($field) != $result) {
            $this->reject(I18n::translate('uniform-calc-incorrect'), $field);
        }
        $this->form->forget($field);
    }
}
