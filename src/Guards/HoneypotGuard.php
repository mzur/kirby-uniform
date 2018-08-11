<?php

namespace Uniform\Guards;

use Kirby\Cms\App;
use Kirby\Toolkit\I18n;

/**
 * Guard that checks a honeypot form field.
 */
class HoneypotGuard extends Guard
{
    /**
     * Default name for the honeypot form field.
     *
     * @var string
     */
    const FIELD_NAME = 'website';

    /**
     * {@inheritDoc}
     * Check if the honeypot field contains data.
     * Remove the honeypot field from the form data if it was empty.
     */
    public function perform()
    {
        $field = $this->option('field', self::FIELD_NAME);
        if (App::instance()->request()->body()->get($field) !== '') {
            $this->reject(I18n::translate('uniform-filled-potty'), $field);
        }
        $this->form->forget($field);
    }
}
